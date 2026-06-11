<?php

namespace Modules\CRMEmail\Services;

use Illuminate\Support\Facades\DB;
use Modules\CRMEmail\Entities\EmailSegment;

class SegmentResolverService
{
    /**
     * Get the estimated size of recipients for a given unsaved segment criteria.
     *
     * @param array $sources
     * @param array $criteria
     * @return int
     */
    public function estimateCount(array $sources, array $criteria): int
    {
        $query = $this->buildMergedQuery($sources, $criteria);
        
        return DB::table(DB::raw("({$query->toSql()}) as deduplicated"))
            ->mergeBindings($query)
            ->count();
    }

    /**
     * Get sample list of recipients matching criteria (limited to 10 rows).
     *
     * @param array $sources
     * @param array $criteria
     * @param int $limit
     * @return array
     */
    public function getSampleRecipients(array $sources, array $criteria, int $limit = 10): array
    {
        $query = $this->buildMergedQuery($sources, $criteria);
        
        return DB::table(DB::raw("({$query->toSql()}) as sample_list"))
            ->mergeBindings($query)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Resolve the complete Eloquent builder for a saved segment.
     *
     * @param EmailSegment $segment
     * @return \Illuminate\Database\Query\Builder
     */
    public function resolve(EmailSegment $segment)
    {
        return $this->buildMergedQuery($segment->sources, $segment->criteria);
    }

    /**
     * Build the merged union query grouped by email.
     *
     * @param array $sources
     * @param array $criteria
     * @return \Illuminate\Database\Query\Builder
     */
    public function buildMergedQuery(array $sources, array $criteria)
    {
        $queries = [];
        $companyId = company()->id;

        if (in_array('clients', $sources)) {
            $queries[] = $this->getClientQuery($criteria['clients'] ?? [], $companyId);
        }
        if (in_array('leads', $sources)) {
            $queries[] = $this->getLeadQuery($criteria['leads'] ?? [], $companyId);
        }
        if (in_array('contacts', $sources)) {
            $queries[] = $this->getContactQuery($criteria['contacts'] ?? [], $companyId);
        }

        if (empty($queries)) {
            // Return empty query mapping matching schema
            return DB::table('users')
                ->select('users.id as recipient_id', 'users.name', 'users.email', DB::raw("'client' as recipient_type"))
                ->whereRaw('1 = 0');
        }

        // Apply union merge
        $unionQuery = array_shift($queries);
        foreach ($queries as $q) {
            $unionQuery = $unionQuery->union($q);
        }

        // Wrap union to enforce deduplication on email level
        // Grouping ensures distinct email delivery, selecting first matching polymorphic type.
        return DB::table(DB::raw("({$unionQuery->toSql()}) as merged_recipients"))
            ->mergeBindings($unionQuery)
            ->groupBy('email');
    }

    /**
     * Compile Client target subquery.
     */
    private function getClientQuery(array $filters, int $companyId)
    {
        $query = DB::table('users')
            ->select('users.id as recipient_id', 'users.name', 'users.email', DB::raw("'client' as recipient_type"))
            ->join('client_details', 'client_details.user_id', '=', 'users.id')
            ->where('users.company_id', $companyId)
            ->where('users.email_notifications', 1)
            ->whereNull('users.deleted_at')
            ->whereNotIn('users.email', function($q) use ($companyId) {
                $q->select('email')->from('crm_unsubscribes')->where('company_id', $companyId);
            })
            ->whereNotIn('users.email', function($q) use ($companyId) {
                $q->select('email')->from('crm_invalid_emails')->where('company_id', $companyId);
            });

        if (!empty($filters['status'])) {
            $query->whereIn('users.status', $filters['status']);
        }
        if (!empty($filters['category_ids'])) {
            $query->whereIn('client_details.category_id', $filters['category_ids']);
        }
        if (!empty($filters['sub_category_ids'])) {
            $query->whereIn('client_details.sub_category_id', $filters['sub_category_ids']);
        }
        if (!empty($filters['country_ids'])) {
            $query->whereIn('users.country_id', $filters['country_ids']);
        }
        if (!empty($filters['city'])) {
            $query->where('client_details.city', 'like', '%' . $filters['city'] . '%');
        }
        if (!empty($filters['state'])) {
            $query->where('client_details.state', 'like', '%' . $filters['state'] . '%');
        }

        return $query;
    }

    /**
     * Compile Lead target subquery.
     */
    private function getLeadQuery(array $filters, int $companyId)
    {
        $query = DB::table('leads')
            ->select('leads.id as recipient_id', 'leads.client_name as name', 'leads.client_email as email', DB::raw("'lead' as recipient_type"))
            ->where('leads.company_id', $companyId)
            ->whereNotIn('leads.client_email', function($q) use ($companyId) {
                $q->select('email')->from('crm_unsubscribes')->where('company_id', $companyId);
            })
            ->whereNotIn('leads.client_email', function($q) use ($companyId) {
                $q->select('email')->from('crm_invalid_emails')->where('company_id', $companyId);
            });

        if (!empty($filters['status_ids'])) {
            $query->whereIn('leads.status_id', $filters['status_ids']);
        }
        if (!empty($filters['source_ids'])) {
            $query->whereIn('leads.source_id', $filters['source_ids']);
        }
        if (!empty($filters['category_ids'])) {
            $query->whereIn('leads.category_id', $filters['category_ids']);
        }
        if (!empty($filters['city'])) {
            $query->where('leads.city', 'like', '%' . $filters['city'] . '%');
        }
        if (!empty($filters['state'])) {
            $query->where('leads.state', 'like', '%' . $filters['state'] . '%');
        }
        if (!empty($filters['country'])) {
            $query->where('leads.country', 'like', '%' . $filters['country'] . '%');
        }

        return $query;
    }

    /**
     * Compile Client Contact target subquery.
     */
    private function getContactQuery(array $filters, int $companyId)
    {
        $query = DB::table('client_contacts')
            ->select('client_contacts.id as recipient_id', 'client_contacts.contact_name as name', 'client_contacts.email', DB::raw("'contact' as recipient_type"))
            ->join('users', 'client_contacts.user_id', '=', 'users.id')
            ->where('client_contacts.company_id', $companyId)
            ->whereNull('users.deleted_at')
            ->whereNotIn('client_contacts.email', function($q) use ($companyId) {
                $q->select('email')->from('crm_unsubscribes')->where('company_id', $companyId);
            })
            ->whereNotIn('client_contacts.email', function($q) use ($companyId) {
                $q->select('email')->from('crm_invalid_emails')->where('company_id', $companyId);
            });

        if (!empty($filters['designations'])) {
            $query->where(function($q) use ($filters) {
                foreach ($filters['designations'] as $designation) {
                    $q->orWhere('client_contacts.title', 'like', '%' . $designation . '%');
                }
            });
        }
        if (!empty($filters['parent_client_status'])) {
            $query->whereIn('users.status', $filters['parent_client_status']);
        }

        return $query;
    }
}
