@if (in_array('birthday', $activeWidgets) && in_array('employees', user_modules()))
    <!-- EMP DASHBOARD PAST BIRTHDAY START -->
    <div class="col-sm-12">
        <x-cards.data class="e-d-info mb-3" title="Past Birthdays (Last 6 Months)" padding="false"
                      otherClasses="h-200">
            <x-table>
                @forelse ($pastBirthdays as $pastBirthday)
                    <tr>
                        <td class="pl-20">
                            <x-employee :user="$pastBirthday->user"/>
                        </td>
                        <td>
                            <span class="badge badge-light p-2">
                                <i class="fa fa-birthday-cake"></i>
                                {{ $pastBirthday->date_of_birth->translatedFormat('d M') }}
                            </span>
                        </td>
                        <td class="pr-20" align="right">
                            @php
                                $currentYear = now(company()->timezone)->year;
                                $dobMonthDay = $pastBirthday->date_of_birth->format('m-d');
                                $currentMonthDay = now(company()->timezone)->format('m-d');
                                
                                // Determine the correct year for the past birthday
                                if ($dobMonthDay > $currentMonthDay) {
                                    $yearToUse = $currentYear - 1;
                                } else {
                                    $yearToUse = $currentYear;
                                }
                                
                                $dateBirth = $pastBirthday->date_of_birth->timezone(company()->timezone)->year($yearToUse);

                                $diffInDays = $dateBirth->copy()->diffForHumans(now()->timezone(company()->timezone),[
                                    'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_AUTO,
                                    'options' => \Carbon\Carbon::JUST_NOW | \Carbon\Carbon::ONE_DAY_WORDS | \Carbon\Carbon::TWO_DAY_WORDS,
                                ]);

                            @endphp

                            @if ($dateBirth->isToday())
                                <span class="badge badge-light text-success p-2"><i class="fa fa-smile"></i> @lang('app.today')</span>
                            @else
                                <span class="badge badge-light p-2">{{ $diffInDays }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="shadow-none">
                            <x-cards.no-record icon="birthday-cake" :message="__('messages.noRecordFound')"/>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-cards.data>
    </div>
    <!-- EMP DASHBOARD PAST BIRTHDAY END -->
@endif
