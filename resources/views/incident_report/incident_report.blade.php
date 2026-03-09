@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Incident Report
@stop
@section("styles")
<style>
    th{
        text-align: center;
    }
    .btn-check{
        display:none;
    }

    .select2-container {
    z-index: 99999 !important;
    }

    .modal .select2-dropdown {
    z-index: 99999 !important;
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
            <h4 class="m-0">Incident Report</h4>
            <label>{{date('D, d M Y')}}</label>
        </div>

        {{-- Table Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Incident Report List</h5>
                        @if(preg_match("/C/i", Auth::user()->access[Route::current()->action["as"]]["access"]))
                        <button class="btn btn-success btn-sm" id="btn_add_ir">
                            <i class="fa fa-plus"></i> File Incident Report
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="ir_table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Case Number</th>
                                        <th>Reported By</th>
                                        <th>Incident Date</th>
                                        <th>Location</th>
                                        <th>Names Involved</th>
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

{{-- Add IR Modal --}}
<div class="modal fade" id="modal_add_ir" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Incident Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Reported By <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="reported_by" style="width:100%">
                                <option value="">Select Employee</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" class="form-control" id="complainant_position" readonly placeholder="Auto-filled">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date & Time of Report <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="report_datetime">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date of Incident <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="incident_date">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" placeholder="Where did it happen?">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Incident <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="incident" rows="4" placeholder="Describe what happened..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Names Involved <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="names_involved" multiple style="width:100%">
                            </select>
                            <small class="text-muted">You can select multiple employees</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Witnesses</label>
                            <input type="text" class="form-control" id="witnesses" placeholder="Names of witnesses (optional)">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btn_save_ir">
                    <i class="fa fa-save"></i> Submit Report
                </button>
            </div>
        </div>
    </div>
</div>

{{-- View IR Modal --}}
<div class="modal fade" id="modal_view_ir" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Incident Report — <span id="view_case_number"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="view_ir_body">
                {{-- Loaded via AJAX --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success d-none" id="btn_mark_reviewed">
                    <i class="fa fa-check"></i> Mark as Reviewed
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit IR Modal --}}
<div class="modal fade" id="modal_edit_ir" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Incident Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Reported By <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="edit_reported_by" style="width:100%"></select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" class="form-control" id="edit_complainant_position" readonly placeholder="Auto-filled">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date & Time of Report <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="edit_report_datetime">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date of Incident <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_incident_date">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_location" placeholder="Where did it happen?">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Incident <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_incident" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Names Involved <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="edit_names_involved" multiple style="width:100%"></select>
                            <small class="text-muted">You can select multiple employees</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Witnesses</label>
                            <input type="text" class="form-control" id="edit_witnesses" placeholder="Names of witnesses (optional)">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btn_update_ir">
                    <i class="fa fa-save"></i> Update Report
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
    var ir_table = $('#ir_table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("ir.list") }}',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'case_number' },
            { data: 'reported_by_name' },
            { data: 'incident_date' },
            { data: 'location' },
            { data: 'names_involved' },
            { data: 'status', render: function(data){
                if(data == 'pending'){
                    return '<span class="badge badge-warning">Pending</span>';
                } else {
                    return '<span class="badge badge-success">Reviewed</span>';
                }
            }},
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // Initialize Select2 for Reported By
    $('#reported_by').select2({
        dropdownParent: $('#modal_add_ir'),
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

    // Initialize Select2 for Names Involved (multiple)
    $('#names_involved').select2({
        dropdownParent: $('#modal_add_ir'),
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

    // Auto-fill position when reported_by is selected
    $('#reported_by').on('select2:select', function(e){
        var data = e.params.data;
        $('#complainant_position').val(data.position);
    });

    // Open Add IR Modal
    $('#btn_add_ir').on('click', function(){
        // Reset form
        $('#reported_by').val(null).trigger('change');
        $('#names_involved').val(null).trigger('change');
        $('#complainant_position').val('');
        $('#report_datetime').val('');
        $('#incident_date').val('');
        $('#location').val('');
        $('#incident').val('');
        $('#witnesses').val('');
        $('#modal_add_ir').modal('show');
    });

    // Save IR
    $('#btn_save_ir').on('click', function(){
        var reported_by    = $('#reported_by').val();
        var names_involved = $('#names_involved').val();
        var report_datetime = $('#report_datetime').val();
        var incident_date  = $('#incident_date').val();
        var location       = $('#location').val();
        var incident       = $('#incident').val();

        // Basic validation
        if(!reported_by){
            alert('Please select the complainant.'); return;
        }
        if(!names_involved || names_involved.length == 0){
            alert('Please select at least one involved employee.'); return;
        }
        if(!report_datetime || !incident_date || !location || !incident){
            alert('Please fill in all required fields.'); return;
        }

        HoldOn.open({ theme: 'sk-circle' });

        $.ajax({
            url: '{{ route("ir.store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reported_by: reported_by,
                complainant_position: $('#complainant_position').val(),
                report_datetime: report_datetime,
                incident_date: incident_date,
                location: location,
                incident: incident,
                names_involved: names_involved,
                witnesses: $('#witnesses').val()
            },
            success: function(response){
                HoldOn.close();
                if(response.success){
                    $('#modal_add_ir').modal('hide');
                    ir_table.ajax.reload();
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

        // Select2 for Edit Modal - Reported By
    $('#edit_reported_by').select2({
        dropdownParent: $('#modal_edit_ir'),
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

    // Select2 for Edit Modal - Names Involved
    $('#edit_names_involved').select2({
        dropdownParent: $('#modal_edit_ir'),
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

    // Auto-fill position when edit_reported_by is selected
    $('#edit_reported_by').on('select2:select', function(e){
        var data = e.params.data;
        $('#edit_complainant_position').val(data.position);
    });

// View IR Action Button Click
$(document).on('click', '.btn_view_ir', function(){
    var id = $(this).data('id');

    HoldOn.open({ theme: 'sk-circle' });

    $.ajax({
        url: '/ir/view/' + id,
        type: 'GET',
        success: function(response){
            HoldOn.close();
            if(response.success){
                var ir = response.data;
                var involved = ir.involved.map(function(e){ return e.name; }).join(', ');

                $('#view_case_number').text(ir.case_number);

                // Show/hide Mark as Reviewed button
                if(ir.status == 'pending'){
                    $('#btn_mark_reviewed').removeClass('d-none').data('id', ir.id);
                } else {
                    $('#btn_mark_reviewed').addClass('d-none');
                }

                $('#view_ir_body').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <label class="font-weight-bold">Reported By</label>
                            <p>${ir.reported_by_name}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold">Position</label>
                            <p>${ir.complainant_position ?? 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold">Date & Time of Report</label>
                            <p>${ir.report_datetime}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold">Date of Incident</label>
                            <p>${ir.incident_date}</p>
                        </div>
                        <div class="col-md-12">
                            <label class="font-weight-bold">Location</label>
                            <p>${ir.location}</p>
                        </div>
                        <div class="col-md-12">
                            <label class="font-weight-bold">Incident</label>
                            <p>${ir.incident}</p>
                        </div>
                        <div class="col-md-12">
                            <label class="font-weight-bold">Names Involved</label>
                            <p>${involved}</p>
                        </div>
                        <div class="col-md-12">
                            <label class="font-weight-bold">Witnesses</label>
                            <p>${ir.witnesses ?? 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold">Status</label>
                            <p>${ir.status == 'pending' ? '<span class="badge badge-warning">Pending</span>' : '<span class="badge badge-success">Reviewed</span>'}</p>
                        </div>
                    </div>
                `);

                $('#modal_view_ir').modal('show');
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

// Mark as Reviewed
$(document).on('click', '#btn_mark_reviewed', function(){
    var id = $(this).data('id');

    $.confirm({
        title: 'Mark as Reviewed',
        content: 'Are you sure you want to mark this Incident Report as reviewed?',
        type: 'green',
        buttons: {
            confirm: {
                text: 'Yes, Mark as Reviewed',
                btnClass: 'btn-success',
                action: function(){
                    HoldOn.open({ theme: 'sk-circle' });

                    $.ajax({
                        url: '/ir/review/' + id,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response){
                            HoldOn.close();
                            if(response.success){
                                $('#modal_view_ir').modal('hide');
                                ir_table.ajax.reload();
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


// Edit IR - Open Modal
$(document).on('click', '.btn_edit_ir', function(){
    var id = $(this).data('id');

    HoldOn.open({ theme: 'sk-circle' });

    $.ajax({
        url: '/ir/view/' + id,
        type: 'GET',
        success: function(response){
            HoldOn.close();
            if(response.success){
                var ir = response.data;

                // Set the IR id on the save button
                $('#btn_update_ir').data('id', ir.id);

                // Fill in the fields
                $('#edit_report_datetime').val(ir.report_datetime);
                $('#edit_incident_date').val(ir.incident_date);
                $('#edit_location').val(ir.location);
                $('#edit_incident').val(ir.incident);
                $('#edit_witnesses').val(ir.witnesses);
                $('#edit_complainant_position').val(ir.complainant_position);

                // Fill reported_by Select2
                var reported_by_option = new Option(ir.reported_by_name, ir.reported_by, true, true);
                $('#edit_reported_by').append(reported_by_option).trigger('change');

                // Fill names_involved Select2 (multiple)
                $('#edit_names_involved').empty();
                $.each(ir.involved, function(i, emp){
                    var option = new Option(emp.name, emp.id, true, true);
                    $('#edit_names_involved').append(option);
                });
                $('#edit_names_involved').trigger('change');

                $('#modal_edit_ir').modal('show');
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

// Edit IR - Save
$(document).on('click', '#btn_update_ir', function(){
    var id             = $(this).data('id');
    var reported_by    = $('#edit_reported_by').val();
    var names_involved = $('#edit_names_involved').val();
    var report_datetime = $('#edit_report_datetime').val();
    var incident_date  = $('#edit_incident_date').val();
    var location       = $('#edit_location').val();
    var incident       = $('#edit_incident').val();

    if(!reported_by){
        alert('Please select the complainant.'); return;
    }
    if(!names_involved || names_involved.length == 0){
        alert('Please select at least one involved employee.'); return;
    }
    if(!report_datetime || !incident_date || !location || !incident){
        alert('Please fill in all required fields.'); return;
    }

    HoldOn.open({ theme: 'sk-circle' });

    $.ajax({
        url: '/ir/update/' + id,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            reported_by:           reported_by,
            complainant_position:  $('#edit_complainant_position').val(),
            report_datetime:       report_datetime,
            incident_date:         incident_date,
            location:              location,
            incident:              incident,
            names_involved:        names_involved,
            witnesses:             $('#edit_witnesses').val()
        },
        success: function(response){
            HoldOn.close();
            if(response.success){
                $('#modal_edit_ir').modal('hide');
                ir_table.ajax.reload();
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

// Delete IR Action
$(document).on('click', '.btn_delete_ir', function(){
    var id = $(this).data('id');

    $.confirm({
        title: 'Delete Incident Report',
        content: 'Are you sure you want to delete this Incident Report? This action cannot be undone.',
        type: 'red',
        buttons: {
            confirm: {
                text: 'Yes, Delete',
                btnClass: 'btn-danger',
                action: function(){
                    HoldOn.open({ theme: 'sk-circle' });

                    $.ajax({
                        url: '/ir/delete/' + id,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response){
                            HoldOn.close();
                            if(response.success){
                                ir_table.ajax.reload();
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