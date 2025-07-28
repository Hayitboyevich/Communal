<?php

namespace Modules\Water\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Decision extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'protocol_id',
        'event_id',
        'created_time',
        'updated_time',
        'region_id',
        'district_id',
        'organ_id',
        'protocol_article_part',
        'inspector_pinpp',
        'latitude',
        'longitude',
        'series',
        'number',
        'decision_series',
        'decision_number',
        'status',
        'status_name',
        'last_name',
        'first_name',
        'second_name',
        'document_series',
        'document_number',
        'pinpp',
        'birth_date',
        'employment_place',
        'employment_position',
        'decision_type_id',
        'decision_type_name',
        'execution_date',
        'main_punishment_type',
        'main_punishment_amount',
        'resolution_organ_id',
        'adm_case_organ_id',
        'resolution_organ',
        'adm_case_organ',
        'resolution_consider_info',
        'discount_amount_70',
        'discount_amount_50',
        'discount_amount_30',
        'discount_for_date_70',
        'discount_for_date_50',
        'discount_for_date_30',
        'termination_reason_id',
        'paid_amount',
        'decision_status'
    ];
}
