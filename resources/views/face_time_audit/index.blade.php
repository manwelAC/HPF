@extends('layouts.front-app')

@section('title')

{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Payroll Management

@stop

@section("styles")

<!-- Include Daterangepicker CSS -->

<link rel="stylesheet" type="text/css" href="{{ asset_with_env('assets/css/daterangepicker.css')}}" />

<style>

    .image-gallery {

        display: flex;

        flex-wrap: wrap;

        gap: 20px;

        justify-content: center;

    }

    .gallery-item {

        width: 200px;

        text-align: center;

    }

    .gallery-item img {

        width: 100%;

        height: auto;

        border-radius: 8px;

    }

    .gallery-item p {

        font-size: 14px;

        margin-top: 8px;

    }

    .pagination {

        text-align: center;

        margin-top: 20px;

    }

    .pagination button {

        padding: 10px 20px;

        margin: 0 5px;

        background-color: #2f47ba;

        color: white;

        border: none;

        cursor: pointer;

    }

    .pagination button:disabled {

        background-color: grey;

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

                        <div class="col-xl-10 col-sm-8 col-12 ">

                            <label >YOU HAVE NO PRIVILEGE ON THIS PAGE </label>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@else

<div class="page-wrapper" id="face_time_audit_page">

    <div class="content container-fluid">

        <div class="col-xl-12 col-sm-12 col-12">

            <div class="card oth_income_card oth_library">

                <div class="card-header" style="background-color: #2f47ba;">

                    <h2 class="card-titles" style="color: white;">Face and Time Audit <i style="float:right; cursor: pointer;" id="oth_library-ico" class="oth_library-ico"></i></h2>

                </div>

                

                <div class="row">

                    <div class="col-xl-12 col-sm-12 col-12">

                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-3">

                                    <select id="emp_list" class="form-control form-select w-100">

                                        <option value="0">Select Employee</option>

                                        @foreach($tbl_employee as $emp)

                                            <option value="{{$emp->emp_code}}">{{$emp->emp_code}} - {{$emp->last_name}}, {{$emp->first_name}} {{$emp->middle_name}} {{$emp->ext_name}}</option>

                                        @endforeach

                                    </select>

                                </div>

                            </div><br>

                            <div class="row" id="datepicker_div">

                                <div class="col-md-4">

                                    <input type="text" id="date_range" class="form-control" placeholder="Select Date Range" />

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                

                <div class="row">

                    <div class="col-xl-12 col-sm-12 col-12">

                        <div class="card-body">

                            <div class="row">

                                <div class="col-xl-12 col-sm-12 col-12">

                                    <div id="image-gallery" class="image-gallery">

                                        <!-- Gallery Items will be dynamically loaded here -->

                                    </div>



                                    <div id="pagination" class="pagination">

                                        <button id="prev-btn" disabled>Prev</button>

                                        <button id="next-btn">Next</button>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endif

@stop



@section("scripts")

<!-- Include Moment.js -->

<script src="{{ asset_with_env('assets/js/moment.min.js')}}"></script>

<!-- Include Daterangepicker JS -->

<script src="{{ asset_with_env('assets/js/daterangepicker.min.js')}}"></script>

<script>

    $(document).ready(function() {
        var user_count = {{ count($tbl_employee) }};
        var curr_id = @json($tbl_employee[0]->emp_code ?? null);

        $('#emp_list').select2({ width: '100%' });

        if (user_count === 1 && curr_id !== null) {
           
            if ($('#emp_list option[value="' + curr_id + '"]').length) {
                $('#emp_list').val(curr_id).trigger('change');
            } else {
                console.warn('No emp_list found', curr_id);
            }
        }

        var currentPage = 1;

        var totalImages = 0;

        var imagesPerPage = 20;



        // Initialize the daterangepicker

		$('#date_range').daterangepicker({

			locale: {

				format: 'YYYY-MM-DD',

			},

			autoUpdateInput: false,

		});



		// Update table when the user selects a date range

		$('#date_range').on('apply.daterangepicker', function (ev, picker) {

			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));

			loadFaceTimeAuditImages();

		});

		//cancel date range

		$('#date_range').on('cancel.daterangepicker', function () {

			$(this).val('');

			loadFaceTimeAuditImages();

		});



        function loadFaceTimeAuditImages() {

            var empId = $("#emp_list").val();

            

            $.ajax({

                url: "{{ route('load_face_time_audit_tbl') }}",

                type: "POST",

                dataType: "json",

                data: {

                    "_token": "{{ csrf_token() }}", 

                    "emp_id": empId,

                    "date_range": $('#date_range').val(),

                    "page": currentPage,

                    "limit": imagesPerPage

                },

                success: function(response) {

                    totalImages = response.total_images; // Assuming total_images is returned in the response

                    renderGallery(response.images);

                    updatePaginationButtons();

                }

            });

        }



        function renderGallery(images) {

            if(images){

                var galleryHtml = '';

                images.forEach(function(image) {

                    galleryHtml += `

                        <div class="gallery-item">

                            <img src="https://hfp-face.intra-code.com/${image.image}" alt="${image.name}">

                            <p>${image.state} <br> ${image.created_at}</p>

                        </div>

                    `;

                });

                $('#image-gallery').html(galleryHtml);

            }

        }



        function updatePaginationButtons() {

            $("#prev-btn").prop("disabled", currentPage === 1);

            $("#next-btn").prop("disabled", currentPage * imagesPerPage >= totalImages);

        }



        $("#emp_list").on("change", function() {

            currentPage = 1; // Reset to first page when employee changes

            loadFaceTimeAuditImages();

        });



        $("#next-btn").on("click", function() {

            if (currentPage * imagesPerPage < totalImages) {

                currentPage++;

                loadFaceTimeAuditImages();

            }

        });



        $("#prev-btn").on("click", function() {

            if (currentPage > 1) {

                currentPage--;

                loadFaceTimeAuditImages();

            }

        });



        loadFaceTimeAuditImages(); // Initial load

    });

</script>

@stop