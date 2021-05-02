<?php

namespace YnievesDotNetTeam\SwipeLaraslider\Controller;

use App\Http\Controllers\Controller;
use YnievesDotNetTeam\SwipeLaraslider\Slider;
use YnievesDotNetTeam\SwipeLaraslider\SliderImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use YnievesDotNetTeam\SwipeLaraslider\Helpers\EloquentHelpers;
use Illuminate\Support\Traits\Macroable;

class SliderController extends Controller
{
    use Macroable;

    protected $slider;

    protected $sliderImage;
    protected $laravelSlider;

    public function __construct(Slider $slider, SliderImage $sliderImage)
    {
        $this->slider = $slider;
        $this->sliderImage = $sliderImage;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sliders = $this->slider->orderBy('id', 'desc')->with('slides')->get();

        return view('swipe-laraslider::index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('swipe-laraslider::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        // Get list of file names from request as array [temp storage]
        $sliderImages = $data['image_name'];
        $sliderName = $data['name'];

        // Move SliderImages from temp storage to original storage
        $oldPath = 'temp/'.$sliderName.'/';
        $targetPath = 'slide';
        $resultFiles = EloquentHelpers::moveAllFiles($sliderImages, $oldPath, $targetPath, $sliderName);

        $path = $data['name'].'/';
        try {
            $newSlider = $this->slider->create($data);
        } catch (Exception $e) {
            return redirect('swipe-laraslider::create')->with('error', $e->getMessage())->withInput();
        }
        $date = $request->image_name;

        foreach ($data['slides'] as $slide) {
            $slide['slider_id'] = $newSlider->id;
            try {
                $this->sliderImage->create($slide);
            } catch (Exception $e) {
                return redirect('swipe-laraslider::create')->with('error', $e->getMessage())->withInput();
            }
        }

        return redirect('slider')->with('success', 'Slider saved successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $currentDate = Carbon::now();
        $currentFormatedDate = $currentDate->format('Y-m-d');
        $slider = $this->slider->where('id', $id)->first();

        $slides = \DB::select("
            SELECT * from slider_images 
            where is_active = 1 AND slider_id = ? AND ? BETWEEN DATE_FORMAT(start_date, '%Y-%m-%d') AND DATE_FORMAT(end_date, '%Y-%m-%d')", [$slider->id, $currentFormatedDate]);

        return view('swipe-laraslider::show', compact('slides', 'slider'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $slider = $this->slider->where('id', $id)->with('slides')->first();

        return view('swipe-laraslider::edit', compact('slider'));
    }

    public function preview(Request $request)
    {
        $selectedFiles = $request->all();
        $folderName = 'sliders';
        $path = 'temp/'.$folderName.'/';

        foreach ($selectedFiles as $file) {
            $images[] = EloquentHelpers::uploadFile($path, $file, 'public');
        }

        return view('swipe-laraslider::preview', compact('selectedFiles', 'folderName', 'images'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $slider = $this->slider->where('id', $id)->first();
        DB::beginTransaction();
        if (!$slider) {
            return redirect('slider')->with('error', 'Slider details does not exists.');
        }

        $data = $request->all();
        $data = $request->except(['_token', '_method']);

        if ($slider) {
            $slider->fill($data);
            try {
                $slider->save();
            } catch (QueryException $e) {
                DB::rollback();

                return redirect('swipe-laraslider::edit')->with('error', $e->getMessage())->withInput();
            }
        } else {
            return redirect('swipe-laraslider::index')->with('error', $e->getMessage());
        }

        //get list of slides id from request
        $inputSlidesIds = collect($data['oldSlides'])->pluck('id')->all();

        //get list of slides from the database for perticular slider
        $existingSlidesIds = $this->sliderImage->where('slider_id', $id)->pluck('id')->all();

        //get Difference between input and existing slides id
        $toBeDeletedSlidesIds = array_diff($existingSlidesIds, $inputSlidesIds);

        // Delete those slides which are found in the above difference
        $this->sliderImage->where('slider_id', $id)->whereIn('id', $toBeDeletedSlidesIds)->delete();

        foreach ($data['oldSlides'] as $slide) {
            $slides = $this->sliderImage->where('slider_id', $id)->where('id', $slide['id'])->first();
            if ($slides) {
                $slides->fill($slide);
                try {
                    $slides->save();
                } catch (QueryException $e) {
                    DB::rollback();

                    return redirect('swipe-laraslider::edit')->with('error', $e->getMessage())->withInput();
                }
            } else {
                try {
                    $this->sliderImage->create($slide);
                } catch (QueryException $e) {
                    DB::rollback();

                    return redirect('swipe-laraslider::index')->with('error', $e->getMessage());
                }
            }
        }

        //New Slide Added druing edit methode
        if (array_has($data, 'slides')) {
            // Get list of file names from request as array [temp storage]
            $sliderImages = $data['image_name'];

            $sliderName = $data['name'];
            // Move SliderImages from temp storage to original storage
            $oldPath = 'temp/'.$sliderName.'/';
            $targetPath = 'slide';
            $resultFiles = EloquentHelpers::moveAllFiles($sliderImages, $oldPath, $targetPath, $sliderName);

            foreach ($data['slides'] as $slide) {
                $slide['slider_id'] = $id;
                try {
                    $this->sliderImage->create($slide);
                } catch (Exception $e) {
                    DB::rollback();

                    return redirect('swipe-laraslider::edit')->with('error', $e->getMessage())->withInput();
                }
            }
        }
        DB::commit();

        return redirect('slider')->with('success', 'Slider details updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = [];
        $slider = $this->slider->findOrFail($id)->first();

        try {
            $this->slider->where('id', $id)->delete();
        } catch (Exception $e) {
            return redirect('/slider')->with('error', $e->getMessage());
        }

        return redirect('/slider')->with('success', 'Slider deleted successfully');
    }

    public function changeSliderStatus($id)
    {
        $status = $this->slider->where('id', $id)->pluck('is_active')->first();
        if ($status == 1) {
            try {
                $this->slider->find($id)->update(['is_active' => false]);

                return redirect('/slider');
            } catch (Exception $e) {
                return redirect('swipe-laraslider::index')->with('error', $e->getMessage());
            }
        } else {
            try {
                $this->slider->find($id)->update(['is_active' => true]);

                return redirect('/slider');
            } catch (Exception $e) {
                return redirect('swipe-laraslider::index')->with('error', $e->getMessage());
            }
        }
    }

    // public function get($entityType, $entityId)
    // {
    //     if (isset($entityType)) {
    //         if ($entityType == 'slider' && isset($entityId)) {
    //             $sliders = $this->slider->where('entity_type', $entityType)->where('entity_id', $entityId)->first();
    //         } else {
    //             $sliders = $this->slider->join('slider_entities')->where('entity_type', $entityType)->where('entity_id', $entityId)->get();
    //         }
    //     } else {
    //         $sliders = $this->slider->where('entity_type', $entityType)->get();
    //     }
    //     return view('swipe-laraslider::show', compact('sliders'));
    // }

    // public function sliderEntities(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $this->SliderEntity->create($request->all())
    //     } catch (Exception $e) {
    //         return redirect('/slider')->with('error', $e->getMessage())->withInput();
    //     }

    //     DB::commit();
    //     return redirect('/slider')->with('success', 'Slider stored successfully.');
    // }
}
