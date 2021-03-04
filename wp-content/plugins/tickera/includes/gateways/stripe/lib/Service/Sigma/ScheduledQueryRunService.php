<?php

// File generated from our OpenAPI spec

namespace TCStripe\Service\Sigma;

class ScheduledQueryRunService extends \TCStripe\Service\AbstractService
{
    /**
     * Returns a list of scheduled query runs.
     *
     * @param null|array $params
     * @param null|array|\TCStripe\Util\RequestOptions $opts
     *
     * @throws \TCStripe\Exception\ApiErrorException if the request fails
     *
     * @return \TCStripe\Collection
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/sigma/scheduled_query_runs', $params, $opts);
    }

    /**
     * Retrieves the details of an scheduled query run.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\TCStripe\Util\RequestOptions $opts
     *
     * @throws \TCStripe\Exception\ApiErrorException if the request fails
     *
     * @return \TCStripe\Sigma\ScheduledQueryRun
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/sigma/scheduled_query_runs/%s', $id), $params, $opts);
    }
}
