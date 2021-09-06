<?php

namespace App\Admin\Controllers;

use App\VehicleCategory;
use App\Status;
use App\Models\DailyFareManagement;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DailyFareManagementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Daily Fare Management';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DailyFareManagement());

        $grid->column('id', __('Id'));
        $grid->column('vehicle_type', __('Vehicle Type'))->display(function($vehicle_type){
            return VehicleCategory::where('id',$vehicle_type)->value('vehicle_type');
        });
        $grid->column('base_fare', __('Base Fare'));
        $grid->column('price_per_km', __('Price Per Km'));
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } else {
                return "<span class='label label-danger'>$status_name</span>";
            }
        $grid->column('created_at', __('Created at'))->hide();
        $grid->column('updated_at', __('Updated at'))->hide();
        });
        
        $grid->disableExport();
        //$grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
         $grid->filter(function ($filter) {
        
            $statuses = Status::where('type','general')->pluck('name','id');
            $vehiclecategories = VehicleCategory::pluck('vehicle_type', 'id');
         
            
            $filter->equal('vehicle_type', 'Vehicle Type')->select($vehiclecategories);
            $filter->equal('status', 'Status')->select($statuses);
        
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(DailyFareManagement::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('vehicle_type', __('Vehicle type'));
        $show->field('base_fare', __('Base fare'));
        $show->field('price_per_km', __('Price per km'));
        $show->field('status', __('Status'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DailyFareManagement());
        
        $statuses = Status::where('type','general')->pluck('name','id');
        $vehiclecategories = VehicleCategory::pluck('vehicle_type', 'id');
         
        $form->select('vehicle_type', __('Vehicle type'))->options($vehiclecategories)->rules('required');
        $form->decimal('base_fare', __('Base Fare'))->rules('required');
        $form->decimal('price_per_km', __('Price Per Km'))->rules('required');
        $form->select('status', __('Status'))->options($statuses)->rules('required');
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete(); 
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
