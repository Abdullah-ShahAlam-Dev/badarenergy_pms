<div class="card w-100 rounded-0 border-0 comment">
    <div class="card-horizontal">
        <div class="card-body border-0 pl-0 py-1">
            <x-table class="table-bordered my-3 rounded">
                <x-slot name="thead">
                    <th>@lang('modules.leaves.leaveType')</th>
                    <th>@lang('modules.leaves.noOfLeaves') (Allocated)</th>
                    <th>Leaves Used</th>
                    <th class="text-right">Leaves Remaining</th>
                    <th class="text-right">@lang('modules.leaves.monthLimit')</th>
                </x-slot>

                @php $hasRecord = false; @endphp
                @foreach ($leaveTypes as $key => $leave)
                    @if($leave->leaveTypeCodition($leave, $userRole))
                        @php
                            $hasRecord = true;
                            // Safe resolution of the leave quota record
                            $quotaRecord = $employeeLeavesQuotas->firstWhere('leave_type_id', $leave->id);
                            $allowedLeaves = $quotaRecord ? $quotaRecord->no_of_leaves : ($leave->employeeLeave ?? $leave->no_of_leaves ?? 0);
                            
                            $usedLeaves = $leave->leavesCount ? ($leave->leavesCount->count - ($leave->leavesCount->halfday * 0.5)) : 0;
                            $remainingLeaves = max(0, $allowedLeaves - $usedLeaves);
                        @endphp
                        <tr>
                            <td width="20%">
                                <x-status :value="$leave->type_name" :style="'color:'.$leave->color" />
                            </td>
                            <td width="20%">{{ $allowedLeaves }}</td>
                            <td width="20%">{{ $usedLeaves }}</td>
                            <td width="20%" class="font-weight-bold text-success text-right">{{ $remainingLeaves }}</td>
                            <td width="20%" class="text-right">{{ ($leave->monthly_limit > 0) ? $leave->monthly_limit : '--' }}</td>
                        </tr>
                    @endif
                @endforeach

                @if(!$hasRecord)
                    <tr>
                        <td colspan="5" class="text-center">
                            <x-cards.no-record icon="redo" :message="__('messages.noRecordFound')" />
                        </td>
                    </tr>
                @endif
            </x-table>
        </div>
    </div>
</div>
