@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Notice to Explain
@stop
@section("styles")
<style>
    th{
        text-align: center;
    }
    .btn-check{
        display:none;
    }
</style>
@stop
@section("content")
@if(preg_match("/R/i", Auth::user()->access[Route::current()->action["as"]]["access"])=="0")
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-xl-12 col-sm-12 col-12 mb-4">
                    <div class="row">
                        <div class="col-xl-10 col-sm-8 col-12">
                            <label>YOU HAVE NO PRIVILEDGE ON THIS PAGE</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
<div class="page-wrapper">
    <div class="content container-fluid">

        {{-- Page Header --}}
        <div class="page-name mb-4">
            <h4 class="m-0">Notice to Explain</h4>
            <label>{{date('D, d M Y')}}</label>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0">NTE List</h5>
                        @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] == 'hr')
                        <button class="btn btn-success btn-sm" id="btn_add_nte">
                            <i class="fa fa-plus"></i> Create NTE
                        </button>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="nte_table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Case Number</th>
                                        <th>IR Case Number</th>
                                        <th>Employee</th>
                                        <th>Date Served</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loaded via AJAX --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- View NTE Modal --}}
<div class="modal fade" id="modal_view_nte" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View NTE — <span id="view_nte_case_number"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="view_nte_body">
                {{-- Loaded via AJAX --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                {{-- Employee reply button - only shows if status is pending and user is employee --}}
                <button type="button" class="btn btn-success d-none" id="btn_open_reply">
                    <i class="fa fa-reply"></i> Submit Explanation
                </button>
                {{-- HR: Issue DA button - only shows if status is replied --}}
                <button type="button" class="btn btn-danger d-none" id="btn_issue_da">
                    <i class="fa fa-gavel"></i> Issue Disciplinary Action
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit NTE Modal --}}
<div class="modal fade" id="modal_edit_nte" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit NTE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date Served <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_nte_date_served">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_nte_due_date">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Case Details <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_nte_case_details" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" id="edit_nte_remarks" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Resolution</label>
                            <textarea class="form-control" id="edit_nte_resolution" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btn_update_nte">
                    <i class="fa fa-save"></i> Update NTE
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Employee Reply Modal --}}
<div class="modal fade" id="modal_reply_nte" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Explanation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Your Explanation <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="employee_reply" rows="6" placeholder="Write your explanation here..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btn_submit_reply">
                    <i class="fa fa-paper-plane"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Standalone NTE Modal --}}
<div class="modal fade" id="modal_add_nte" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create NTE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Employee <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="add_nte_employee_id" style="width:100%"></select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date Served <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="add_nte_date_served">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="add_nte_due_date">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Case Details <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="add_nte_case_details" rows="4" placeholder="Details of the case..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" id="add_nte_remarks" rows="3" placeholder="Admin remarks (optional)..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Resolution</label>
                            <textarea class="form-control" id="add_nte_resolution" rows="3" placeholder="Resolution (optional)..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btn_save_standalone_nte">
                    <i class="fa fa-save"></i> Save NTE
                </button>
            </div>
        </div>
    </div>
</div>

@endif
@stop
@section("scripts")
<script>
$(document).ready(function(){

    // Initialize DataTable
        var nte_table = $('#nte_table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("nte.list") }}',
                type: 'GET',
                dataSrc: 'data'
            },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'case_number' },
            { data: 'ir_case_number' },
            { data: 'employee_name' },
            { data: 'date_served' },
            { data: 'due_date' },
            { data: 'status', render: function(data){
                if(data == 'pending'){
                    return '<span class="badge badge-warning">Pending</span>';
                } else if(data == 'replied'){
                    return '<span class="badge badge-info">Replied</span>';
                } else {
                    return '<span class="badge badge-success">Closed</span>';
                }
            }},
            { data: 'action', orderable: false, searchable: false }
        ]
    });


    // Initialize Select2 for standalone NTE employee
$('#add_nte_employee_id').select2({
    dropdownParent: $('#modal_add_nte'),
    ajax: {
        url: '{{ route("ir.search_employee") }}',
        type: 'GET',
        dataType: 'json',
        delay: 250,
        data: function(params){
            return { search: params.term };
        },
        processResults: function(data){
            return { results: data };
        }
    },
    placeholder: '-- Search Employee --',
    minimumInputLength: 1
});

// Open Add NTE Modal
$('#btn_add_nte').on('click', function(){
    $('#add_nte_employee_id').val(null).trigger('change');
    $('#add_nte_date_served').val('');
    $('#add_nte_due_date').val('');
    $('#add_nte_case_details').val('');
    $('#add_nte_remarks').val('');
    $('#add_nte_resolution').val('');
    $('#modal_add_nte').modal('show');
});

    // Save Standalone NTE
    $('#btn_save_standalone_nte').on('click', function(){
        var employee_id  = $('#add_nte_employee_id').val();
        var date_served  = $('#add_nte_date_served').val();
        var due_date     = $('#add_nte_due_date').val();
        var case_details = $('#add_nte_case_details').val();

        if(!employee_id){
            alert('Please select an employee.'); return;
        }
        if(!date_served || !due_date || !case_details){
            alert('Please fill in all required fields.'); return;
        }

        HoldOn.open({ theme: 'sk-circle' });

        $.ajax({
            url: '{{ route("nte.store") }}',
            type: 'POST',
            data: {
                _token:       '{{ csrf_token() }}',
                ir_id:        null, // standalone, no IR
                employee_id:  employee_id,
                date_served:  date_served,
                due_date:     due_date,
                case_details: case_details,
                remarks:      $('#add_nte_remarks').val(),
                resolution:   $('#add_nte_resolution').val()
            },
            success: function(response){
                HoldOn.close();
                if(response.success){
                    $('#modal_add_nte').modal('hide');
                    nte_table.ajax.reload();
                    $.notify({ message: response.message }, { type: 'success' });
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                }
            },
            error: function(){
                HoldOn.close();
                $.notify({ message: 'Something went wrong. Please try again.' }, { type: 'danger' });
            }
        });
    });

    // View NTE
    $(document).on('click', '.btn_view_nte', function(){
        var id = $(this).data('id');

        HoldOn.open({ theme: 'sk-circle' });

        $.ajax({
            url: '/nte/view/' + id,
            type: 'GET',
            success: function(response){
                HoldOn.close();
                if(response.success){
                    var nte = response.data;

                    $('#view_nte_case_number').text(nte.case_number);

                    // Show/hide reply button (employee side, only if pending)
                    @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] == 'employee')
                    if(nte.status == 'pending'){
                        $('#btn_open_reply').removeClass('d-none').data('id', nte.id);
                    } else {
                        $('#btn_open_reply').addClass('d-none');
                    }
                    @endif

                    // Show/hide Issue DA button (HR side, pending or replied)
                    @if(Auth::user()->access[Route::current()->action["as"]]["user_type"] == 'hr')
                    if(nte.status == 'pending' || nte.status == 'replied'){
                        $('#btn_issue_da').removeClass('d-none').data('id', nte.id).data('nte', nte);
                    } else {
                        $('#btn_issue_da').addClass('d-none');
                    }
                    @endif

                    $('#view_nte_body').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <label class="font-weight-bold">Case Number</label>
                                <p>${nte.case_number}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold">IR Case Number</label>
                                <p>${nte.ir_case_number}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold">Employee</label>
                                <p>${nte.employee_name}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold">Status</label>
                                <p>
                                    ${nte.status == 'pending' ? '<span class="badge badge-warning">Pending</span>' :
                                      nte.status == 'replied' ? '<span class="badge badge-info">Replied</span>' :
                                      '<span class="badge badge-success">Closed</span>'}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold">Date Served</label>
                                <p>${nte.date_served ?? 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="font-weight-bold">Due Date</label>
                                <p>${nte.due_date ?? 'N/A'}</p>
                            </div>
                            <div class="col-md-12">
                                <label class="font-weight-bold">Case Details</label>
                                <p>${nte.case_details}</p>
                            </div>
                            <div class="col-md-12">
                                <label class="font-weight-bold">Remarks</label>
                                <p>${nte.remarks ?? 'N/A'}</p>
                            </div>
                            <div class="col-md-12">
                                <label class="font-weight-bold">Resolution</label>
                                <p>${nte.resolution ?? 'N/A'}</p>
                            </div>
                            ${nte.employee_reply ? `
                            <div class="col-md-12">
                                <hr>
                                <label class="font-weight-bold">Employee Explanation</label>
                                <p>${nte.employee_reply}</p>
                                <small class="text-muted">Submitted on: ${nte.reply_date}</small>
                            </div>` : ''}
                        </div>
                    `);

                    $('#modal_view_nte').modal('show');
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                }
            },
            error: function(){
                HoldOn.close();
                $.notify({ message: 'Something went wrong. Please try again.' }, { type: 'danger' });
            }
        });
    });

    // Open Reply Modal
    $(document).on('click', '#btn_open_reply', function(){
        var id = $(this).data('id');
        $('#btn_submit_reply').data('id', id);
        $('#employee_reply').val('');
        $('#modal_reply_nte').modal('show');
    });

    // Submit Employee Reply
    $(document).on('click', '#btn_submit_reply', function(){
        var id    = $(this).data('id');
        var reply = $('#employee_reply').val();

        if(!reply){
            alert('Please write your explanation before submitting.'); return;
        }

        HoldOn.open({ theme: 'sk-circle' });

        $.ajax({
            url: '/nte/reply/' + id,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                employee_reply: reply
            },
            success: function(response){
                HoldOn.close();
                if(response.success){
                    $('#modal_reply_nte').modal('hide');
                    $('#modal_view_nte').modal('hide');
                    nte_table.ajax.reload();
                    $.notify({ message: response.message }, { type: 'success' });
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                }
            },
            error: function(){
                HoldOn.close();
                $.notify({ message: 'Something went wrong. Please try again.' }, { type: 'danger' });
            }
        });
    });

    // Edit NTE - Open Modal
    $(document).on('click', '.btn_edit_nte', function(){
        var id = $(this).data('id');

        HoldOn.open({ theme: 'sk-circle' });

        $.ajax({
            url: '/nte/view/' + id,
            type: 'GET',
            success: function(response){
                HoldOn.close();
                if(response.success){
                    var nte = response.data;
                    $('#btn_update_nte').data('id', nte.id);
                    $('#edit_nte_date_served').val(nte.date_served);
                    $('#edit_nte_due_date').val(nte.due_date);
                    $('#edit_nte_case_details').val(nte.case_details);
                    $('#edit_nte_remarks').val(nte.remarks);
                    $('#edit_nte_resolution').val(nte.resolution);
                    $('#modal_edit_nte').modal('show');
                }
            },
            error: function(){
                HoldOn.close();
                $.notify({ message: 'Something went wrong. Please try again.' }, { type: 'danger' });
            }
        });
    });

    // Update NTE
    $(document).on('click', '#btn_update_nte', function(){
        var id           = $(this).data('id');
        var date_served  = $('#edit_nte_date_served').val();
        var due_date     = $('#edit_nte_due_date').val();
        var case_details = $('#edit_nte_case_details').val();

        if(!date_served || !due_date || !case_details){
            alert('Please fill in all required fields.'); return;
        }

        HoldOn.open({ theme: 'sk-circle' });

        $.ajax({
            url: '/nte/update/' + id,
            type: 'POST',
            data: {
                _token:       '{{ csrf_token() }}',
                date_served:  date_served,
                due_date:     due_date,
                case_details: case_details,
                remarks:      $('#edit_nte_remarks').val(),
                resolution:   $('#edit_nte_resolution').val()
            },
            success: function(response){
                HoldOn.close();
                if(response.success){
                    $('#modal_edit_nte').modal('hide');
                    nte_table.ajax.reload();
                    $.notify({ message: response.message }, { type: 'success' });
                } else {
                    $.notify({ message: response.message }, { type: 'danger' });
                }
            },
            error: function(){
                HoldOn.close();
                $.notify({ message: 'Something went wrong. Please try again.' }, { type: 'danger' });
            }
        });
    });

    // Delete NTE
    $(document).on('click', '.btn_delete_nte', function(){
        var id = $(this).data('id');

        $.confirm({
            title: 'Delete NTE',
            content: 'Are you sure you want to delete this NTE? This action cannot be undone.',
            type: 'red',
            buttons: {
                confirm: {
                    text: 'Yes, Delete',
                    btnClass: 'btn-danger',
                    action: function(){
                        HoldOn.open({ theme: 'sk-circle' });

                        $.ajax({
                            url: '/nte/delete/' + id,
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response){
                                HoldOn.close();
                                if(response.success){
                                    nte_table.ajax.reload();
                                    $.notify({ message: response.message }, { type: 'success' });
                                } else {
                                    $.notify({ message: response.message }, { type: 'danger' });
                                }
                            },
                            error: function(){
                                HoldOn.close();
                                $.notify({ message: 'Something went wrong. Please try again.' }, { type: 'danger' });
                            }
                        });
                    }
                },
                cancel: {
                    text: 'Cancel',
                    btnClass: 'btn-secondary'
                }
            }
        });
    });

});
</script>
@stop