<?php

namespace App\Admin\Controllers;

use App\Complaint;
use App\Status;
use App\Customer;
use App\Driver;
use App\ComplaintCategory;
use App\ComplaintSubCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ComplaintController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Complaints';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Complaint);

        $grid->column('id', __('Id'));
        $grid->column('trip_id', __('Trip id'));
        $grid->column('customer_id', __('Customer id'))->display(function(){
            $value = Customer::where('id',$this->customer_id)->value('full_name');
            return $value;
        });
        $grid->column('driver_id', __('Driver id'))->display(function(){
            $value = Driver::where('id',$this->driver_id)->value('full_name');
            return $value;
        });
        $grid->column('complaint_category', __('Complaint category'))->display(function(){
            $value = ComplaintCategory::where('id',$this->complaint_category)->value('complaint_category_name');
            return $value;
        });

        $grid->column('complaint_sub_category', __('Complaint sub category'))->display(function(){
            $value = ComplaintSubCategory::where('id',$this->complaint_sub_category)->value('complaint_sub_category_name');
            return $value;
        });

        $grid->column('description', __('Description'))->hide();
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } else {
                return "<span class='label label-danger'>$status_name</span>";
            }
        });
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('created_at')->hide();
        $grid->column('updated_at')->hide();
        $grid->disableExport();
        //$grid->disableCreateButton();
        $grid->actions(function ($actions) {
        $actions->disableView();
        $actions->disableDelete();
        });
        $grid->filter(function ($filter) {
            $statuses = Status::pluck('name', 'id');
            $customers = Customer::where('status',1)->pluck('full_name','id');
            $drivers = Driver::where('status',1)->pluck('full_name','id');

            $filter->disableIdFilter();
            $filter->like('trip_id', 'Trip id');
            $filter->like('customer_id', 'Customer id')->select($customers);
            $filter->like('driver_id', 'Driver id')->select($drivers);
            $filter->like('complaint_category', 'Complaint category');
            $filter->like('complaint_sub_category', 'Complaint sub category');
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
        $show = new Show(Complaint::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('trip_id', __('Trip id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('driver_id', __('Driver id'));
        $show->field('complaint_category', __('Complaint category'));
        $show->field('complaint_sub_category', __('Complaint sub category'));
        $show->field('description', __('Description'));
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
        $form = new Form(new Complaint);
        $statuses = Status::where('type','general')->pluck('name','id');
        $customers = Customer::where('status',1)->pluck('full_name','id');
        $drivers = Driver::where('status',1)->pluck('full_name','id');
        $complaint_categories = complaintCategory::pluck('complaint_category_name','id');
        $complaint_sub_categories = complaintSubCategory::pluck('complaint_sub_category_name','id');


        $form->number('trip_id', __('Trip id'))->rules('required');
        $form->select('customer_id', __('Customer id'))->options($customers)->rules('required');
        $form->select('driver_id', __('Driver id'))->options($drivers)->rules('required');
        $form->select('complaint_category', __('Complaint category'))->load('complaint_sub_category', '/admin/get_complaint_sub_category', 'id', 'complaint_sub_category_name')->options($complaint_categories)->rules(function ($form) {
            return 'required';
        });
        $form->select('complaint_sub_category', __('Complaint sub scategory'))->options($complaint_sub_categories)->rules('required');
        $form->textarea('description', __('Description'))->rules('required');
        $form->select('status','Status')->options($statuses)->rules('required');

        $form->footer(function ($footer) {
        $footer->disableViewCheck();
        $footer->disableEditingCheck();
        $footer->disableCreatingCheck();

        });

        $form->tools(function (Form\Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
        });

        return $form;
    }
}
