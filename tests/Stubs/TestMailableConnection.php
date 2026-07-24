<?php

namespace Fahlgrendigital\StatamicFormManager\Tests\Stubs;

use Fahlgrendigital\StatamicFormManager\Connector\ConnectorResponse;
use Fahlgrendigital\StatamicFormManager\Connector\MailableConnection;
use Fahlgrendigital\StatamicFormManager\Contracts\SubmissionInterface;

class TestMailableConnection extends MailableConnection
{
    public function callMakeRequest(array $data): ConnectorResponse
    {
        return $this->makeRequest($data);
    }

    public function callPrepData(SubmissionInterface $submission): array
    {
        return $this->prepData($submission);
    }
}
