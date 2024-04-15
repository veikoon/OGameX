@extends('ingame.layouts.main')

@section('content')

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <!-- JAVASCRIPT -->
    <script type="text/javascript">
        function initResources() {
            var load_done = 1;
            gfSlider = new GFSlider(getElementByIdWithCache('planet'));
        }
        var action = 0;
        var id;
        var priceBuilding = 750;
        var priceShips = 750;
        var demolish_id;
        var buildUrl;
        function loadDetails(type) {
            url = "{{ route('resources.index', ['ajax' => 1]) }}";
            if (typeof(detailUrl) != 'undefined') {
                url = detailUrl;
            }
            $.get(url, {type: type}, function (data) {
                $("#detail").html(data);
                $("#techDetailLoading").hide();
                $("input[type='text']:first", document.forms["form"]).focus();
                $(document).trigger("ajaxShowElement", (typeof techID === 'undefined' ? 0 : techID));
            });
        }
        $(document).ready(function () {
            $('#ranks tr').hover(function () {
                $(this).addClass('hover');
            }, function () {
                $(this).removeClass('hover');
            });
        });
        var timeDelta = 1514117983000 - (new Date()).getTime();
        var cancelProduction_id;
        var production_listid;
        function cancelProduction(id, listid, question) {
            cancelProduction_id = id;
            production_listid = listid;
            errorBoxDecision("Caution", "" + question + "", "yes", "No", cancelProductionStart);
        }
        function cancelProductionStart() {
            $('<form id="cancelProductionStart" action="{{ route('research.cancelbuildrequest') }}" method="POST" style="display: none;">{{ csrf_field() }}<input type="hidden" name="building_id" value="' + cancelProduction_id + '" /> <input type="hidden" name="building_queue_id" value="' + production_listid + '" /></form>').appendTo('body').submit();

            //window.location.replace("{!! route('research.cancelbuildrequest') !!}?_token=" + csrfToken + "&techid=" + cancelProduction_id + "&listid=" + production_listid);
        }
        $(document).ready(function () {
            initEventTable();
        });
        var player = {hasCommander: false};
        var detailUrl = "{{ route('research.ajax') }}";

        $(document).ready(function () {
            initResources();
            @if (!empty($build_active))
            // Countdown for inline building element (pusher)
            var elem = getElementByIdWithCache("b_research{{ $build_active->object->id }}");
            if(elem) {
                new bauCountdown(elem, {{ $build_active->time_countdown }}, {{ $build_active->time_total }}, "{{ route('research.index') }}");
            }
            @endif
        });

    </script>

    <div id="eventboxContent" style="display: none">
    <img height="16" width="16" src="/img/icons/3f9884806436537bdec305aa26fc60.gif">
    </div>

    <div id="inhalt">

        <div id="planet" style="background-image:url(img/headers/research/research.jpg)">
            <div id="header_text">
                <h2>Research - {{ $planet_name }}</h2>
            </div>

            <form method="POST" action="{!! route('research.addbuildrequest') !!}" name="form">
                {{ csrf_field() }}
                <div id="detail" class="detail_screen">
                    <div id="techDetailLoading"></div>
                </div>
            </form>

        </div>
        <div class="c-left"></div>
        <div class="c-right"></div>

        <div id="buttonz" class="wrapButtons">
            <div id="wrapBattle" class="resLeft fleft">
                <h2>Basic research</h2>
                <ul id="base1" class="activate">
                    @php /** @var OGame\ViewModels\QueueBuildingViewModel $building */ @endphp
                    @foreach ($research[0] as $building)
                        <li class="@if ($building->currently_building)
                                on
                            @elseif (!$building->requirements_met)
                                off
                            @elseif (!$building->enough_resources)
                                disabled
                            @elseif ($build_queue_max)
                                disabled
                            @else
                                on
                            @endif
                                ">
                            <div class="item_box research{!! $building->object->id !!}">
                                <div class="buildingimg">
                                    @if ($building->requirements_met && $building->enough_resources)
                                        <a class="fastBuild tooltip js_hideTipOnMobile" title="Research {!! $building->object->title !!} level {!! ($building->current_level + 1) !!}" href="javascript:void(0);" onclick="sendBuildRequest('{!! route('research.addbuildrequest.get', ['modus' => 1, 'type' => $building->object->id, 'planet_id' => $planet_id, '_token' => csrf_token()]) !!}', null, 1);">
                                            <img src="/img/icons/3e567d6f16d040326c7a0ea29a4f41.gif" width="22" height="14">
                                        </a>
                                    @endif
                                    @if ($building->currently_building)
                                        <div class="construction">
                                            <div class="pusher" id="b_research{{ $building->object->id }}" style="height:100px;">
                                            </div>
                                            <a class="slideIn timeLink" href="javascript:void(0);" ref="{{ $building->object->id }}">
                                                <span class="time" id="test" name="zeit"></span>
                                            </a>

                                            <a class="detail_button slideIn"
                                               id="details{{ $building->object->id }}"
                                               ref="{{ $building->object->id }}"
                                               href="javascript:void(0);">
            <span class="eckeoben">
                <span style="font-size:11px;" class="undermark"> {{ $building->current_level + 1 }}</span>
            </span>
            <span class="ecke">
                <span class="level">{{ $building->current_level }}</span>
            </span>
                                            </a>
                                        </div>
                                    @endif
                                    <a class="detail_button tooltip js_hideTipOnMobile slideIn" title="{!! $building->object->title !!}" ref="{!! $building->object->id !!}" id="details" href="javascript:void(0);">
                        <span class="ecke">
                            <span class="level">
                               <span class="textlabel">
                                   {!! $building->object->title !!}
                               </span>
                                {!! $building->current_level !!}	                           </span>
                        </span>
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div id="wrapBattle" class="resRight fleft">
                <h2>Drive research</h2>
                <ul id="base2" class="activate">
                    @php /** @var OGame\ViewModels\QueueBuildingViewModel $building */ @endphp
                    @foreach ($research[1] as $building)
                        <li class="@if ($building->currently_building)
                                on
                            @elseif (!$building->requirements_met)
                                off
                            @elseif (!$building->enough_resources)
                                disabled
                            @elseif ($build_queue_max)
                                disabled
                            @else
                                on
                            @endif
                                ">
                            <div class="item_box research{!! $building->object->id !!}">
                                <div class="buildingimg">
                                    @if ($building->requirements_met && $building->enough_resources)
                                        <a class="fastBuild tooltip js_hideTipOnMobile" title="Research {!! $building->object->title !!} level {!! ($building->current_level + 1) !!}" href="javascript:void(0);" onclick="sendBuildRequest('{!! route('research.addbuildrequest.get', ['modus' => 1, 'type' => $building->object->id, 'planet_id' => $planet_id, '_token' => csrf_token()]) !!}', null, 1);">
                                            <img src="/img/icons/3e567d6f16d040326c7a0ea29a4f41.gif" width="22" height="14">
                                        </a>
                                    @endif
                                    @if ($building->currently_building)
                                        <div class="construction">
                                            <div class="pusher" id="b_research{{ $building->object->id }}" style="height:100px;">
                                            </div>
                                            <a class="slideIn timeLink" href="javascript:void(0);" ref="{{ $building->object->id }}">
                                                <span class="time" id="test" name="zeit"></span>
                                            </a>

                                            <a class="detail_button slideIn"
                                               id="details{{ $building->object->id }}"
                                               ref="{{ $building->object->id }}"
                                               href="javascript:void(0);">
            <span class="eckeoben">
                <span style="font-size:11px;" class="undermark"> {{ $building->current_level + 1 }}</span>
            </span>
            <span class="ecke">
                <span class="level">{{ $building->current_level }}</span>
            </span>
                                            </a>
                                        </div>
                                    @endif
                                    <a class="detail_button tooltip js_hideTipOnMobile slideIn" title="{!! $building->object->title !!}" ref="{!! $building->object->id !!}" id="details" href="javascript:void(0);">
                        <span class="ecke">
                            <span class="level">
                               <span class="textlabel">
                                   {!! $building->object->title !!}
                               </span>
                                {!! $building->current_level !!}	                           </span>
                        </span>
                                    </a>
                                </div>
                            </div>
                        </li>
                @endforeach
                </ul>
            </div>        <div id="wrapBattle" class="resLeft fleft">
                <h2>Advanced researches</h2>
                <ul id="base3" class="activate">
                    @php /** @var OGame\ViewModels\QueueBuildingViewModel $building */ @endphp
                    @foreach ($research[2] as $building)
                        <li class="@if ($building->currently_building)
                                on
                            @elseif (!$building->requirements_met)
                                off
                            @elseif (!$building->enough_resources)
                                disabled
                            @elseif ($build_queue_max)
                                disabled
                            @else
                                on
                            @endif
                                ">
                            <div class="item_box research{!! $building->object->id !!}">
                                <div class="buildingimg">
                                    @if ($building->requirements_met && $building->enough_resources)
                                        <a class="fastBuild tooltip js_hideTipOnMobile" title="Research {!! $building->object->title !!} level {!! ($building->current_level + 1) !!}" href="javascript:void(0);" onclick="sendBuildRequest('{!! route('research.addbuildrequest.get', ['modus' => 1, 'type' => $building->object->id, 'planet_id' => $planet_id, '_token' => csrf_token()]) !!}', null, 1);">
                                            <img src="/img/icons/3e567d6f16d040326c7a0ea29a4f41.gif" width="22" height="14">
                                        </a>
                                    @endif
                                    @if ($building->currently_building)
                                        <div class="construction">
                                            <div class="pusher" id="b_research{{ $building->object->id }}" style="height:100px;">
                                            </div>
                                            <a class="slideIn timeLink" href="javascript:void(0);" ref="{{ $building->object->id }}">
                                                <span class="time" id="test" name="zeit"></span>
                                            </a>

                                            <a class="detail_button slideIn"
                                               id="details{{ $building->object->id }}"
                                               ref="{{ $building->object->id }}"
                                               href="javascript:void(0);">
            <span class="eckeoben">
                <span style="font-size:11px;" class="undermark"> {{ $building->current_level + 1 }}</span>
            </span>
            <span class="ecke">
                <span class="level">{{ $building->current_level }}</span>
            </span>
                                            </a>
                                        </div>
                                    @endif
                                    <a class="detail_button tooltip js_hideTipOnMobile slideIn" title="{!! $building->object->title !!}" ref="{!! $building->object->id !!}" id="details" href="javascript:void(0);">
                        <span class="ecke">
                            <span class="level">
                               <span class="textlabel">
                                   {!! $building->object->title !!}
                               </span>
                                {!! $building->current_level !!}
                            </span>
                        </span>
                                    </a>
                                </div>
                            </div>
                        </li>
                @endforeach
                </ul>
            </div>        <div id="wrapBattle" class="resRight fleft">
                <h2>Combat research</h2>
                <ul id="base4" class="activate">
                    @php /** @var OGame\ViewModels\QueueBuildingViewModel $building */ @endphp
                    @foreach ($research[3] as $building)
                        <li class="@if ($building->currently_building)
                                on
                            @elseif (!$building->requirements_met)
                                off
                            @elseif (!$building->enough_resources)
                                disabled
                            @elseif ($build_queue_max)
                                disabled
                            @else
                                on
                            @endif
                                ">
                            <div class="item_box research{!! $building->object->id !!}">
                                <div class="buildingimg">
                                    @if ($building->requirements_met && $building->enough_resources)
                                        <a class="fastBuild tooltip js_hideTipOnMobile" title="Research {!! $building->object->title !!} level {!! ($building->current_level + 1) !!}" href="javascript:void(0);" onclick="sendBuildRequest('{!! route('research.addbuildrequest.get', ['modus' => 1, 'type' => $building->object->id, 'planet_id' => $planet_id, '_token' => csrf_token()]) !!}', null, 1);">
                                            <img src="/img/icons/3e567d6f16d040326c7a0ea29a4f41.gif" width="22" height="14">
                                        </a>
                                    @endif
                                    @if ($building->currently_building)
                                        <div class="construction">
                                            <div class="pusher" id="b_research{{ $building->object->id }}" style="height:100px;">
                                            </div>
                                            <a class="slideIn timeLink" href="javascript:void(0);" ref="{{ $building->object->id }}">
                                                <span class="time" id="test" name="zeit"></span>
                                            </a>

                                            <a class="detail_button slideIn"
                                               id="details{{ $building->object->id }}"
                                               ref="{{ $building->object->id }}"
                                               href="javascript:void(0);">
            <span class="eckeoben">
                <span style="font-size:11px;" class="undermark"> {{ $building->current_level + 1 }}</span>
            </span>
            <span class="ecke">
                <span class="level">{{ $building->current_level }}</span>
            </span>
                                            </a>
                                        </div>
                                    @endif
                                    <a class="detail_button tooltip js_hideTipOnMobile slideIn" title="{!! $building->object->title !!}" ref="{!! $building->object->id !!}" id="details" href="javascript:void(0);">
                        <span class="ecke">
                            <span class="level">
                               <span class="textlabel">
                                   {!! $building->object->title !!}
                               </span>
                                {!! $building->current_level !!}	                           </span>
                        </span>
                                    </a>
                                </div>
                            </div>
                        </li>
                @endforeach
                </ul>
            </div>        <br class="clearfloat">
        </div>    <div class="content-box-s">
            <div class="header"><h3>Research</h3></div>
            <div class="content">
                <table cellspacing="0" cellpadding="0" class="construction active">
                    <tbody>
                    {{-- Building is actively being built. --}}
                    @if (!empty($build_active))
                        <tr>
                            <th colspan="2">{!! $build_active->object->title !!}</th>
                        </tr>
                        <tr class="data">
                            <td class="first" rowspan="3">
                                <div>
                                    <a href="javascript:void(0);" class="tooltip js_hideTipOnMobile" style="display: block;" onclick="cancelProduction({!! $build_active->object->id !!},{!! $build_active->id !!},&quot;Cancel expansion of {!! $build_active->object->title !!} to level {!! $build_active->level_target !!}?&quot;); return false;" title="">
                                        <img class="queuePic" width="40" height="40" src="{!! asset('img/objects/research/' . $build_active->object->assets->imgSmall) !!}" alt="{!! $build_active->object->title !!}">
                                    </a>
                                    <a href="javascript:void(0);" class="tooltip abortNow js_hideTipOnMobile" onclick="cancelProduction({!! $build_active->object->id !!},{!! $build_active->id !!},&quot;Cancel expansion of {!! $build_active->object->title !!} to level {!! $build_active->level_target !!}?&quot;); return false;" title="Cancel expansion of {!! $build_active->object->title !!} to level {!! $build_active->level_target !!}?">
                                        <img src="/img/icons/3e567d6f16d040326c7a0ea29a4f41.gif" height="15" width="15">
                                    </a>
                                </div>
                            </td>
                            <td class="desc ausbau">Improve to						<span class="level">Level {!! $build_active->level_target !!}</span>
                            </td>
                        </tr>
                        <tr class="data">
                            <td class="desc">Duration:</td>
                        </tr>
                        <tr class="data">
                            <td class="desc timer">
                                <span id="Countdown">Loading...</span>
                                <!-- JAVASCRIPT -->
                                <script type="text/javascript">
                                    var timerHandler=new TimerHandler();
                                    new baulisteCountdown(getElementByIdWithCache("Countdown"), {!! $build_active->time_countdown !!}, "{!! route('research.index') !!}");
                                </script>
                            </td>
                        </tr>
                        <tr class="data">
                            <td colspan="2">
                                <a class="build-faster dark_highlight tooltipLeft js_hideTipOnMobile building disabled" title="Reduces construction time by 50% of the total construction time (15s)." href="javascript:void(0);" rel="{{ route('shop.index', ['buyAndActivate' => 'cb4fd53e61feced0d52cfc4c1ce383bad9c05f67']) }}">
                                    <div class="build-faster-img" alt="Halve time"></div>
                                    <span class="build-txt">Halve time</span>
                            <span class="dm_cost overmark">
                                Costs: 750 DM                            </span>
                                    <span class="order_dm">Purchase Dark Matter</span>
                                </a>
                            </td>
                        </tr>
                    @endif

                    {{-- Building queue has items. --}}
                    @php /** @var array<OGame\ViewModels\QueueResearchQueueListViewModel> $build_queue */ @endphp
                    @if (count($build_queue) > 0)
                        <table class="queue">
                            <tbody><tr>
                            @php /** @var OGame\ViewModels\QueueResearchQueueViewModel $item */ @endphp
                            @foreach ($build_queue as $item)
                                    <td>
                                        <a href="javascript:void(0);" class="queue_link tooltip js_hideTipOnMobile dark_highlight_tablet" onclick="cancelProduction({!! $item->object->id !!},{!! $item->id !!},&quot;Cancel expansion of {!! $item->object->title !!} to level {!! $item->object->title !!}?&quot;); return false;" title="">
                                            <img class="queuePic" src="{!! asset('img/objects/research/' . $item->object->assets->imgMicro) !!}" height="28" width="28" alt="{!! $item->object->title !!}">
                                            <span>{!! $item->level_target !!}</span>
                                        </a>
                                    </td>
                                @endforeach
                            </tr>
                            </tbody></table>
                    @endif

                    {{-- No buildings are being built. --}}
                    @if (empty($build_active))
                        <tr>
                            <td colspan="2" class="idle">
                                <a class="tooltip js_hideTipOnMobile
                           " title="There is no research done at the moment. Click here to get to your research lab." href="{{ route('research.index') }}">
                                    There is no research in progress at the moment.
                                </a>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="footer"></div>
        </div>
    </div>

@endsection
