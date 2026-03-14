@extends('layouts.front-app')
@section('title')
{{Auth::user()->access[Route::current()->action["as"]]["user_type"]}} - Dashboard
@stop
@section("styles")
<style>
	th{
		text-align: center;
	}
    .btn-check{
       display:none;
    }
    .dz-success-mark{
        display: none;
    }
    .dz-error-mark{
        display: none;
    }
   
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

    .birthday-widget {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        font-family: 'Nunito', sans-serif;
    }

    /* ── Header ── */
    .bday-header {
        background: linear-gradient(120deg, #ff6b35, #f7971e, #ffd200);
        padding: 16px 20px 14px;
        position: relative;
        overflow: hidden;
    }
    .bday-header::before {
        content: '🎂';
        position: absolute;
        right: -8px;
        top: -10px;
        font-size: 72px;
        opacity: 0.13;
        transform: rotate(-15deg);
        pointer-events: none;
    }
    .bday-header-title {
        font-size: 15px;
        font-weight: 800;
        color: #fff;
        letter-spacing: 0.3px;
        margin: 0;
        text-shadow: 0 1px 3px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .bday-header-sub {
        font-size: 11px;
        color: rgba(255,255,255,0.78);
        margin-top: 3px;
        font-weight: 600;
    }

    /* ── Body ── */
    .bday-body {
        background: #fff;
        padding: 14px 16px 16px;
    }

    /* ── Section label ── */
    .bday-section-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #bbb;
        margin-bottom: 10px;
        font-family: 'Nunito', sans-serif;
    }
    .bday-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #f0f0f0;
    }

    /* ── Today card ── */
    .bday-today-card {
        display: flex;
        align-items: center;
        gap: 13px;
        background: linear-gradient(135deg, #fff8f0, #fff3e0);
        border: 1.5px solid #ffe0b2;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 8px;
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .bday-today-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(247,151,30,0.18);
    }
    .bday-today-card .confetti-bg {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 36px;
        opacity: 0.12;
        pointer-events: none;
    }

    /* Avatar */
    .bday-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2.5px solid #f7971e;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(247,151,30,0.25);
    }
    .bday-avatar-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f7971e, #ffd200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 17px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(247,151,30,0.3);
        font-family: 'Nunito', sans-serif;
        text-shadow: 0 1px 2px rgba(0,0,0,0.15);
    }

    .bday-today-card .bday-name {
        font-weight: 800;
        font-size: 13.5px;
        color: #2d2d2d;
        line-height: 1.3;
        font-family: 'Nunito', sans-serif;
    }
    .bday-today-card .bday-meta {
        font-size: 11.5px;
        color: #f7971e;
        font-weight: 600;
        margin-top: 2px;
    }
    .bday-today-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(90deg, #f7971e, #ffd200);
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        padding: 3px 9px;
        border-radius: 20px;
        margin-top: 5px;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        box-shadow: 0 2px 6px rgba(247,151,30,0.35);
        font-family: 'Nunito', sans-serif;
    }

    /* ── Upcoming list ── */
    .bday-upcoming-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .bday-upcoming-list li {
        display: flex;
        align-items: center;
        gap: 11px;
        background: #fafafa;
        border: 1px solid #f0f0f0;
        border-radius: 10px;
        padding: 9px 12px;
        transition: background 0.15s, transform 0.15s;
    }
    .bday-upcoming-list li:hover {
        background: #fff8f0;
        border-color: #ffe0b2;
        transform: translateX(2px);
    }

    /* Countdown pill */
    .bday-days-pill {
        flex-shrink: 0;
        min-width: 42px;
        height: 42px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fff3e0, #ffe0b2);
        border: 1.5px solid #ffcc80;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-family: 'Nunito', sans-serif;
    }
    .bday-days-pill .bday-days-num {
        font-size: 17px;
        font-weight: 800;
        color: #e65100;
        line-height: 1;
    }
    .bday-days-pill .bday-days-label {
        font-size: 9px;
        font-weight: 700;
        color: #f7971e;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .bday-days-pill.is-tomorrow {
        background: linear-gradient(135deg, #f7971e, #ffd200);
        border-color: #f7971e;
    }
    .bday-days-pill.is-tomorrow .bday-days-num,
    .bday-days-pill.is-tomorrow .bday-days-label {
        color: #fff;
    }

    .bday-upcoming-list .bday-info {
        flex: 1;
        min-width: 0;
    }
    .bday-upcoming-list .bday-info .bday-name {
        font-weight: 700;
        font-size: 13px;
        color: #2d2d2d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: 'Nunito', sans-serif;
    }
    .bday-upcoming-list .bday-info .bday-date {
        font-size: 11px;
        color: #aaa;
        font-weight: 600;
        margin-top: 1px;
    }

    /* ── Empty state ── */
    .bday-empty {
        text-align: center;
        padding: 22px 10px;
    }
    .bday-empty-icon {
        font-size: 36px;
        display: block;
        margin-bottom: 8px;
        opacity: 0.45;
    }
    .bday-empty-text {
        font-size: 13px;
        color: #ccc;
        font-weight: 600;
        font-family: 'Nunito', sans-serif;
    }

    /* ── Fade-in animation ── */
    @keyframes bdayFadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .bday-today-card,
    .bday-upcoming-list li {
        animation: bdayFadeUp 0.3s ease both;
    }
    .bday-today-card:nth-child(1)       { animation-delay: 0.05s; }
    .bday-today-card:nth-child(2)       { animation-delay: 0.10s; }
    .bday-upcoming-list li:nth-child(1) { animation-delay: 0.08s; }
    .bday-upcoming-list li:nth-child(2) { animation-delay: 0.13s; }
    .bday-upcoming-list li:nth-child(3) { animation-delay: 0.18s; }
    .bday-upcoming-list li:nth-child(4) { animation-delay: 0.23s; }
    .bday-upcoming-list li:nth-child(5) { animation-delay: 0.28s; }

    /* ── Anniversary Widget ── */
    .anniversary-widget {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        font-family: 'Nunito', sans-serif;
    }

    .anniv-header {
        background: linear-gradient(120deg, #667eea, #764ba2, #f093fb);
        padding: 16px 20px 14px;
        position: relative;
        overflow: hidden;
    }
    .anniv-header::before {
        content: '🎖️';
        position: absolute;
        right: -8px;
        top: -10px;
        font-size: 72px;
        opacity: 0.13;
        transform: rotate(-15deg);
        pointer-events: none;
    }
    .anniv-header-title {
        font-size: 15px;
        font-weight: 800;
        color: #fff;
        letter-spacing: 0.3px;
        margin: 0;
        text-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .anniv-header-sub {
        font-size: 11px;
        color: rgba(255,255,255,0.78);
        margin-top: 3px;
        font-weight: 600;
    }

    .anniv-body {
        background: #fff;
        padding: 14px 16px 16px;
    }

    .anniv-section-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #bbb;
        margin-bottom: 10px;
        font-family: 'Nunito', sans-serif;
    }
    .anniv-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #f0f0f0;
    }

    .anniv-today-card {
        display: flex;
        align-items: center;
        gap: 13px;
        background: linear-gradient(135deg, #f0e6ff, #f5e6ff);
        border: 1.5px solid #e6d5ff;
        border-radius: 12px;
        padding: 12px 14px;
        margin-bottom: 8px;
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .anniv-today-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(118,75,162,0.18);
    }
    .anniv-today-card .confetti-bg {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 36px;
        opacity: 0.12;
        pointer-events: none;
    }

    .anniv-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2.5px solid #667eea;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(102,126,234,0.25);
    }
    .anniv-avatar-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 17px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(102,126,234,0.3);
        font-family: 'Nunito', sans-serif;
        text-shadow: 0 1px 2px rgba(0,0,0,0.15);
    }

    .anniv-today-card .anniv-name {
        font-weight: 800;
        font-size: 13.5px;
        color: #2d2d2d;
        line-height: 1.3;
        font-family: 'Nunito', sans-serif;
    }
    .anniv-today-card .anniv-meta {
        font-size: 11.5px;
        color: #667eea;
        font-weight: 600;
        margin-top: 2px;
    }
    .anniv-today-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        padding: 3px 9px;
        border-radius: 20px;
        margin-top: 5px;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        box-shadow: 0 2px 6px rgba(102,126,234,0.35);
        font-family: 'Nunito', sans-serif;
    }

    .anniv-upcoming-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .anniv-upcoming-list li {
        display: flex;
        align-items: center;
        gap: 11px;
        background: #fafafa;
        border: 1px solid #f0f0f0;
        border-radius: 10px;
        padding: 9px 12px;
        transition: background 0.15s, transform 0.15s;
    }
    .anniv-upcoming-list li:hover {
        background: #f0e6ff;
        border-color: #e6d5ff;
        transform: translateX(2px);
    }

    .anniv-days-pill {
        flex-shrink: 0;
        min-width: 42px;
        height: 42px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f0e6ff, #e6d5ff);
        border: 1.5px solid #d4c0ff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-family: 'Nunito', sans-serif;
    }
    .anniv-days-pill .anniv-days-num {
        font-size: 17px;
        font-weight: 800;
        color: #764ba2;
        line-height: 1;
    }
    .anniv-days-pill .anniv-days-label {
        font-size: 9px;
        font-weight: 700;
        color: #667eea;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .anniv-days-pill.is-tomorrow {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-color: #667eea;
    }
    .anniv-days-pill.is-tomorrow .anniv-days-num,
    .anniv-days-pill.is-tomorrow .anniv-days-label {
        color: #fff;
    }

    .anniv-upcoming-list .anniv-info {
        flex: 1;
        min-width: 0;
    }
    .anniv-upcoming-list .anniv-info .anniv-name {
        font-weight: 700;
        font-size: 13px;
        color: #2d2d2d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: 'Nunito', sans-serif;
    }
    .anniv-upcoming-list .anniv-info .anniv-years {
        font-size: 11px;
        color: #667eea;
        font-weight: 600;
        margin-top: 1px;
    }

    .anniv-empty {
        text-align: center;
        padding: 22px 10px;
    }
    .anniv-empty-icon {
        font-size: 36px;
        display: block;
        margin-bottom: 8px;
        opacity: 0.45;
    }
    .anniv-empty-text {
        font-size: 13px;
        color: #ccc;
        font-weight: 600;
        font-family: 'Nunito', sans-serif;
    }

    @keyframes annivFadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .anniv-today-card,
    .anniv-upcoming-list li {
        animation: annivFadeUp 0.3s ease both;
    }
    .anniv-today-card:nth-child(1)       { animation-delay: 0.05s; }
    .anniv-today-card:nth-child(2)       { animation-delay: 0.10s; }
    .anniv-upcoming-list li:nth-child(1) { animation-delay: 0.08s; }
    .anniv-upcoming-list li:nth-child(2) { animation-delay: 0.13s; }
    .anniv-upcoming-list li:nth-child(3) { animation-delay: 0.18s; }
    .anniv-upcoming-list li:nth-child(4) { animation-delay: 0.23s; }
    .anniv-upcoming-list li:nth-child(5) { animation-delay: 0.28s; }
</style>
@stop
@section("content")
@if(preg_match("/R/i", Auth::user()->access[Route::current()->action["as"]]["access"])=="0")
                            
	{{Auth::user()->access[Route::current()->action["as"]]["access"]}}
	<div class="page-wrapper">
		<div class="content container-fluid">
			<div class="row">
				<div class="col-xl-12 col-sm-12 col-12 mb-4">
					<div class="row">
						<div class="col-xl-10 col-sm-8 col-12 ">
							<label >YOU HAVE NO PRIVILEDGE ON THIS PAGE </label>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@else
<div class="page-wrapper" id="dashboard_page">
    <div class="content container-fluid">
        <div class="page-name 	mb-4">
            <h4 class="m-0">Dashboard</h4>
            <label> {{date('D, d M Y')}}</label>
            
            
        </div>

        <div class="row mb-4">
            <div class="col-xl-9 col-sm-12 col-12" id="statistics_container">
<div class="birthday-widget mb-3">

    {{-- Header --}}
    <div class="bday-header">
        <div class="bday-header-title">Employee Birthdays</div>
        <div class="bday-header-sub">Today &amp; upcoming in the next 7 days</div>
    </div>

    <div class="bday-body">

        {{-- TODAY'S BIRTHDAYS --}}
        @if($todayBirthdays->isNotEmpty())
            <div class="bday-section-label">🎂 Today</div>

            @foreach($todayBirthdays as $emp)
                @php
                    $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                    $age = \Carbon\Carbon::parse($emp->date_of_birth)->age;
                @endphp
                <div class="bday-today-card">
                    <span class="confetti-bg">🎊</span>

                    @if(!empty($emp->profile_picture) && file_exists(public_path($emp->profile_picture)))
                        <img src="{{ asset($emp->profile_picture) }}" class="bday-avatar" alt="{{ $emp->full_name }}">
                    @else
                        <div class="bday-avatar-placeholder">{{ $initials }}</div>
                    @endif

                    <div>
                        <div class="bday-name">{{ $emp->full_name }}</div>
                        <div class="bday-meta">🎈 Turning {{ $age }} today</div>
                        <span class="bday-today-badge">🎁 Happy Birthday!</span>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- UPCOMING BIRTHDAYS --}}
        @if($upcomingBirthdays->isNotEmpty())
            <div class="bday-section-label" style="{{ $todayBirthdays->isNotEmpty() ? 'margin-top:14px;' : '' }}">
                📅 Upcoming
            </div>

            <ul class="bday-upcoming-list">
                @foreach($upcomingBirthdays as $emp)
                    @php
                        $isTomorrow = $emp->days_until === 1;
                        $label = $isTomorrow ? 'tmrw' : 'days';
                        $dateFormatted = \Carbon\Carbon::parse($emp->date_of_birth)->format('M d');
                    @endphp
                    <li>
                        <div class="bday-days-pill {{ $isTomorrow ? 'is-tomorrow' : '' }}">
                            <span class="bday-days-num">{{ $emp->days_until }}</span>
                            <span class="bday-days-label">{{ $label }}</span>
                        </div>
                        <div class="bday-info">
                            <div class="bday-name">{{ $emp->full_name }}</div>
                            <div class="bday-date">📆 {{ $dateFormatted }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- EMPTY STATE --}}
        @if($todayBirthdays->isEmpty() && $upcomingBirthdays->isEmpty())
            <div class="bday-empty">
                <span class="bday-empty-icon">🎈</span>
                <div class="bday-empty-text">No birthdays in the next 7 days</div>
            </div>
        @endif

    </div>
</div>


                
                <div class="row mb-4 ">
                    <div class="col-xl-4 col-sm-12 col-12">
                    
                        <div class="card board1 fill1 ">
                            <div class="card-body">
                                <div class="card_widget_header">
                                    <label>Employees</label>
                                    <h4>{{$tbl_employee}}</h4>
                                </div>
                                <div class="card_widget_img">
                                    <img src="{{asset_with_env('assets/img/dash1.png')}}" alt="card-img" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-12 col-12">
                        <div class="card board1 fill2 ">
                            <div class="card-body">
                                <div class="card_widget_header">
                                    <label>Departments</label>
                                    <h4>{{$department}}</h4>
                                </div>
                                <div class="card_widget_img">
                                    <img src="{{asset_with_env('assets/img/dash2.png')}}" alt="card-img" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-12 col-12">
                        <div class="card board1 fill3 ">
                            <div class="card-body">
                                <div class="card_widget_header">
                                    <label>Leaves</label>
                                    <h4>{{$leave_count}}</h4>
                                </div>
                                <div class="card_widget_img">
                                    <img src="{{asset_with_env('assets/img/dash3.png')}}" alt="card-img" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
						<div class="row mb-4">
							<div class="col-xl-4 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Document Files</label>
											<h4>{{$files}}</h4>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash4.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-4 col-sm-12 col-12">
								<div class="card board1 fill2 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Loans</label>
											<h4>{{$loans}}</h4>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash5.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-4 col-sm-12 col-12">
								<div class="card board1 fill3 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Branches</label>
											<h4>{{$branches}}</h4>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash2.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
						</div>
            </div>
            <div class="col-xl-9 col-sm-12 col-12" id="emp_info_container">
                        
                        <div class="row">
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Position</label>
											<h5 style="color:white;">{{Auth::user()->company["linked_employee"]["position"]}}</h5>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash6.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill2 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Designation</label>
											<h5 style="color:white;">{{Auth::user()->company["linked_employee"]["designation"]}}</h5>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash4.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
                        </div>
							
                        <div class="row mt-2">
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill3 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Department</label>
                                            <h5 style="color:white;">{{Auth::user()->company["linked_employee"]["department"]}}</h5>
											
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash1.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill4 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Branch</label>
											<h5 style="color:white;">{{Auth::user()->company["linked_employee"]["branch"]}}</h5>
										</div>
										<div class="card_widget_img">
											<img src="{{asset_with_env('assets/img/dash2.png')}}" alt="card-img" />
										</div>
									</div>
								</div>
							</div>
                        </div>
                   
                        <div class="row mt-2">
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill1 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Leave Data</label>
											<h5 style="color:white;">{{$leave_count}} - Leave Used </h5>
                                            <h5 style="color:white;">{{$leave_total}} - Leave Credits</h5>
                                            
										</div>
										<div class="card_widget_img">
											<a href="{{route('leave_management')}}"> <img src="{{asset_with_env('assets/img/dash3.png')}}" alt="card-img" /> </a>
										</div>
									</div>
								</div>
							</div>
                            <div class="col-xl-6 col-sm-12 col-12">
								<div class="card board1 fill2 ">
									<div class="card-body">
										<div class="card_widget_header">
											<label>Processed Payroll</label>
											<h5 style="color:white;">{{$payroll_processing}} on process </h5>
                                            <h5 style="color:white;"> {{$payroll_done}} Proccesed </h5>
                                            
										</div>
										<div class="card_widget_img">
										   <a href="{{route('report_management')}}">	<img src="{{asset_with_env('assets/img/dash5.png')}}" alt="card-img" /> </a>
										</div>
									</div>
								</div>
							</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-xl-12 col-sm-12 col-12">
								<div class="card fill4 ">
									<div class="card-body">
                                        <h4 class="text-center" id="logTitle">Today's Log</h4><br>
                                        <table class="table table-striped table-bordered table-hover" id="raw_logs_tbl">
                                            <thead>
                                                <tr>
                                                    <th>Log State</th>
                                                    <th style="width:40%;">Date Time (Log)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
										<!-- <div class="card_widget_header">
											{{-- <label>Leave Data</label> --}}
                                            @if($logs > 0)
                                            <h4> Logged</h4>
                                            @else
                                            <h4>No Current Logs</h4>
                                            @endif
                                            
                                            
										</div>
										<div class="card_widget_img">
											<a href="{{route('timekeeping_management')}}"> <img width="100vw" src="{{asset_with_env('assets/img/profiles/timeIN.png')}}" alt="card-img" /> </a>
										</div> -->
									</div>
								</div>
							</div>
                        </div>
                   
            </div>

            <div class="col-xl-3 col-sm-12 col-12 d-flex">
                <div class="card flex-fill">
                    <div class="dashboard-profile">
                        <div class="dash-imgs text-center" style="background-color:transparent;">
                            <img src="{{ asset_with_env(str_replace('public/', '', Auth::user()->company['linked_employee']['profile_picture'])) }}" alt="profile" onerror="this.onerror=null;this.src='{{ asset_with_env(str_replace('public/', '', Auth::user()->company['logo_sub'])) }}'" />
                            @if(Auth::user()->company["linked_employee"]["id"] != "0")
                            
                            <label>Welcome {{Auth::user()->company["linked_employee"]["name"]}}</label>
                            <span>{{Auth::user()->company["linked_employee"]["position"]}}</span>
                            @else
                            
                            <label>Welcome Admin</label>
                            <span>Administrator</span>
                            @endif
                        </div>
                        <div class="dash-btns">
                            <a id="system_setting" class="btn btn-dashboard" href="{{route('system_management')}}"><i data-feather="settings"
                                    class="mr-1"></i>Settings</a>
                            <a class="btn btn-dashboard" href="{{route('log-out')}}"> <i data-feather="log-out"
                                    class="mr-1"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>


    <div class="col-xl-9 col-sm-12 col-12 anniversary-widget mt-3">

        {{-- Header --}}
        <div class="anniv-header">
            <div class="anniv-header-title">Employee Anniversaries</div>
            <div class="anniv-header-sub">Today &amp; upcoming in the next 7 days</div>
        </div>

        <div class="anniv-body">

            {{-- TODAY'S ANNIVERSARIES --}}
            @if($todayAnniversaries->isNotEmpty())
                <div class="anniv-section-label">🎖️ Today</div>

                @foreach($todayAnniversaries as $emp)
                    @php
                        $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                    @endphp
                    <div class="anniv-today-card">
                        <span class="confetti-bg">🎉</span>

                        @if(!empty($emp->profile_picture) && file_exists(public_path($emp->profile_picture)))
                            <img src="{{ asset($emp->profile_picture) }}" class="anniv-avatar" alt="{{ $emp->full_name }}">
                        @else
                            <div class="anniv-avatar-placeholder">{{ $initials }}</div>
                        @endif

                        <div>
                            <div class="anniv-name">{{ $emp->full_name }}</div>
                            <div class="anniv-meta">🎊 Celebrating {{ $emp->years_service }} {{ $emp->years_service === 1 ? 'Year' : 'Years' }}</div>
                            <span class="anniv-today-badge">🌟 Work Anniversary!</span>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- UPCOMING ANNIVERSARIES --}}
            @if($upcomingAnniversaries->isNotEmpty())
                <div class="anniv-section-label" style="{{ $todayAnniversaries->isNotEmpty() ? 'margin-top:14px;' : '' }}">
                    📅 Upcoming
                </div>

                <ul class="anniv-upcoming-list">
                    @foreach($upcomingAnniversaries as $emp)
                        @php
                            $isTomorrow = $emp->days_until === 1;
                            $label = $isTomorrow ? 'tmrw' : 'days';
                            $dateFormatted = \Carbon\Carbon::parse($emp->start_date)->format('M d');
                        @endphp
                        <li>
                            <div class="anniv-days-pill {{ $isTomorrow ? 'is-tomorrow' : '' }}">
                                <span class="anniv-days-num">{{ $emp->days_until }}</span>
                                <span class="anniv-days-label">{{ $label }}</span>
                            </div>
                            <div class="anniv-info">
                                <div class="anniv-name">{{ $emp->full_name }}</div>
                                <div class="anniv-years">{{ $emp->years_service }} {{ $emp->years_service === 1 ? 'Year' : 'Years' }} of service</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- EMPTY STATE --}}
            @if($todayAnniversaries->isEmpty() && $upcomingAnniversaries->isEmpty())
                <div class="anniv-empty">
                    <span class="anniv-empty-icon">🎊</span>
                    <div class="anniv-empty-text">No work anniversaries in the next 7 days</div>
                </div>
            @endif

        </div>
    </div>

    <div class="col-xl-3 col-sm-12 col-12 mt-3 d-flex">
                <div class="card flex-fill" style="border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.10); display: flex; flex-direction: column; cursor: pointer;" onclick="openAllRegularizationModal()">
                    <div style="background: linear-gradient(120deg, #20c997, #17a2b8, #0dcaf0); padding: 16px 20px 14px; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: -8px; top: -10px; font-size: 72px; opacity: 0.13; transform: rotate(-15deg); pointer-events: none;">📋</div>
                        <h5 style="color: white; font-weight: 800; letter-spacing: 0.3px; margin: 0; text-shadow: 0 1px 3px rgba(0,0,0,0.15);">Employee Regularization</h5>
                        <p style="font-size: 11px; color: rgba(255,255,255,0.78); margin-top: 3px; font-weight: 600;">Ready for evaluation</p>
                    </div>
                    <div style="background: #fff; padding: 14px 16px 16px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="text-align: center;">
                            <div style="font-size: 48px; font-weight: 800; color: #0dcaf0; line-height: 1; margin-bottom: 8px;">{{ $regularizationEmployees->count() }}</div>
                            <div style="font-size: 13px; color: #666; font-weight: 600;">{{ $regularizationEmployees->count() === 1 ? 'Employee' : 'Employees' }} Ready</div>
                            @if($regularizationEmployees->count() > 0)
                                <div style="font-size: 11px; color: #0dcaf0; margin-top: 8px;">Click to review</div>
                            @else
                                <div style="font-size: 11px; color: #ccc; margin-top: 8px;">No pending reviews</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
 
        </div>

        <div class="row" id="graph_container">
            <div class="col-md-6">
                <div id="container" style="min-height: 400px;"></div>
            </div>
            <div class="col-md-6">
                <div id="container_2" style="min-height: 400px;"></div>
            </div> 
        </div>
        
    </div>
</div>
@endif
@stop

<!-- Regularization Modal -->
<div class="modal fade" id="regularizationModal" tabindex="-1" role="dialog" aria-labelledby="regularizationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(120deg, #20c997, #17a2b8, #0dcaf0); border: none;">
                <h5 class="modal-title" id="regularizationModalLabel" style="color: white; font-weight: 800;">Employees Ready for Regularization</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                @if($regularizationEmployees->isNotEmpty())
                    @foreach($regularizationEmployees as $emp)
                        @php
                            $initials = strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1));
                            $isTomorrow = $emp->days_for_regularization === 1;
                            $label = $isTomorrow ? 'tomorrow' : 'days ago';
                        @endphp
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px; padding: 15px; margin-bottom: 12px;">
                            <div style="display: flex; align-items: flex-start; gap: 12px;">
                                <div style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #20c997, #17a2b8); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 18px;">{{ $initials }}</div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 14px; color: #2d2d2d; margin-bottom: 4px;">{{ $emp->full_name }}</div>
                                    <div style="font-size: 12px; color: #666; margin-bottom: 2px;">📅 Started: {{ date('M d, Y', strtotime($emp->start_date)) }}</div>
                                    <div style="font-size: 12px; color: #0dcaf0; font-weight: 600;">Ready for {{ $emp->days_for_regularization }} {{ $label }}</div>
                                    <div style="background: #ffc107; color: #333; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; display: inline-block; margin-top: 6px;">Probationary - 1 Month Complete</div>
                                </div>
                                <button type="button" class="btn btn-success btn-sm" onclick="markEmployeeRegular('{{ $emp->id }}', '{{ $emp->full_name }}')">✓ Make Regular</button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 30px;">
                        <span style="font-size: 48px; display: block; margin-bottom: 10px;">📋</span>
                        <div style="font-size: 16px; color: #ccc; font-weight: 600;">No employees ready for regularization</div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@section("scripts")
<script src="{{asset_with_env('plugins/highcharts/highcharts.js')}}"></script>
<script src="{{asset_with_env('plugins/highcharts/variable-pie.js')}}"></script>
<script src="{{asset_with_env('plugins/highcharts/exporting.js')}}"></script>
<script src="{{asset_with_env('plugins/highcharts/export-data.js')}}"></script>
<script src="{{asset_with_env('plugins/highcharts/accessibility.js')}}"></script>
    <script>
                function getRandomColor() {
                    // Generate random values for red, green, and blue channels
                    const red = Math.floor(Math.random() * 256);
                    const green = Math.floor(Math.random() * 256);
                    const blue = Math.floor(Math.random() * 256);
                    // Create the color string in hexadecimal format
                    const color = '#' + red.toString(16) + green.toString(16) + blue.toString(16);
                    return color;
                }
            $( document ).ready(function() {
                var emp_id = "{{Auth::user()->company['linked_employee']['id']}}";
                var today = new Date().toISOString().split('T')[0]; // Get today's date in 'YYYY-MM-DD' format
                $("#raw_logs_tbl").DataTable({
                    "bDestroy": true,
                    "autoWidth": false,
                    "searchHighlight": true,
                    "searching": true,
                    "processing": true,
                    "serverSide": true,
                    "orderMulti": true,
                    "order": [],
                    "pageLength": 10,
                    "ajax": {
                        "url": "{{ route('raw_logs_tbl') }}",
                        "dataType": "json",
                        "type": "POST",
                        "data":{
                            "_token": "{{ csrf_token() }}", 
                            "page": "{{Route::current()->action['as']}}",  
                            "emp_id": emp_id
                        },
                        "dataSrc": function (json) {
                            // Filter logs to only include today's logs
                            json.data = json.data.filter(function (log) {
                                return log.logs.startsWith(today); // Check if the log date starts with today's date
                            });
                            return json.data;
                        }
                    },
                    "columns":[
                        {'data': 'state'},
                        {'data': 'logs'}
                    ]
                });

                function getCurrentDay() {
                    const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                    const today = new Date();
                    return days[today.getDay()];
                }

                document.getElementById("logTitle").innerText = `Today's Log (${getCurrentDay()})`;
                
                var user_type = "{{Auth::user()->access[Route::current()->action['as']]['user_type']}}";
                if(user_type == "employee"){
                    $("#system_setting").hide("fast");
                    $("#graph_container").hide("fast");
                    $("#statistics_container").hide("fast");
                    $("#emp_info_container").show("fast");
                }else{
                    
                    $("#emp_info_container").hide("fast");
                    $("#graph_container").show("fast");
                    $("#statistics_container").show("fast");
                    $("#system_setting").show("fast");
                }
                $.ajax({
                    url: "{{route('branch_per_emp')}}",
                    data: {
                     
                        _token : "{{csrf_token()}}", 
                    },
                        success: function (source) { 
                            var data_arr = [];
                            var color_arr = [];
                            $.each(source, function( index, value ) {
                                const newItem = {
                                    name: value.name,
                                    y: Number(value.y),
                                };
                                data_arr.push(newItem);
                                color_arr.push(getRandomColor());
                            });
                                    Highcharts.chart('container', {
                                    chart: {
                                        type: 'variablepie'
                                    },
                                    title: {
                                        text: 'Deployed Employee Per Branch',
                                        align: 'left'
                                    },
                                    tooltip: {
                                        headerFormat: '',
                                        pointFormat: '<span style="color:{point.color}">\u25CF</span> <b> {point.name}</b><br/>' +
                                            'Number of Employees: <b>{point.y}</b><br/>'
                                    },
                                    series: [{
                                        minPointSize: 10,
                                        innerSize: '80%',
                                        zMin: 0,
                                        name: 'countries',
                                        borderRadius: 5,
                                        data:data_arr ,
                                        colors: color_arr
                                    }]
                                    });
                            
                        },
                        dataType: 'json',
                        method: 'POST'
                    });
                    $.ajax({
                    url: "{{route('count_mwe')}}",
                    data: {
                     
                        _token : "{{csrf_token()}}", 
                    },
                        success: function (source) { 
                            var data_arr = [];
                            var color_arr = [];
                            $.each(source, function( index, value ) {
                                const newItem = {
                                    name: value.name,
                                    y: Number(value.y),
                                };
                                data_arr.push(newItem);
                                color_arr.push(getRandomColor());
                            });
                                    Highcharts.chart('container_2', {
                                        chart: {
                                        type: 'column'
                                    },
                                    title: {
                                        text: 'Minimum Wage Earners',
                                        align: 'left'
                                    },
                                    xAxis: {
                                        type: 'category',
                                        title: {
                                            text: 'Categories'
                                        }
                                    },
                                    yAxis: {
                                        title: {
                                            text: 'Values'
                                        }
                                    },
                                    tooltip: {
                                        headerFormat: '',
                                        pointFormat: '<span style="color:{point.color}">\u25CF</span> <b> {point.name}</b><br/>' +
                                            'Number of Employees: <b>{point.y}</b><br/>'
                                    },
                                    series: [{
                                        name: 'Number of Employees',
                                        colorByPoint: true, 
                                        data:data_arr,
                                        colors: color_arr 
                                    }]
                                    });
                            
                        },
                        dataType: 'json',
                        method: 'POST'
                    });
            });
  
            // Regularization Modal Functions
            function openAllRegularizationModal() {
                $("#regularizationModal").modal('show');
            }

            function markEmployeeRegular(empId, empName) {
                $.confirm({
                    title: 'Regularize Employee',
                    content: 'Are you sure you want to make ' + empName + ' a regular employee?',
                    escapeKey: 'cancelAction',
                    buttons: {
                        confirm: {
                            text: 'Yes, Regularize',
                            btnClass: 'btn-success',
                            action: function() {
                                $.ajax({
                                    url: "{{ route('mark_employee_regular') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        emp_id: empId
                                    },
                                    type: 'POST',
                                    success: function(response) {
                                        $.notify("Employee regularized successfully", {type:"success", icon:"check"});
                                        location.reload();
                                    },
                                    error: function(error) {
                                        $.notify("Error regularizing employee", {type:"danger", icon:"close"});
                                    }
                                });
                            }
                        },
                        cancel: {
                            text: 'Cancel',
                            btnClass: 'btn-secondary',
                            action: function() {
                                // Nothing
                            }
                        }
                    }
                });
            }
    </script>
@stop