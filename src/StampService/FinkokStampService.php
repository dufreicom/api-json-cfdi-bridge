<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\StampService;

use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\Uuid;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;
use PhpCfdi\Finkok\QuickFinkok;
use PhpCfdi\Finkok\Services\Stamping\StampingResult;
use Throwable;

class FinkokStampService implements StampServiceInterface
{
    public function __construct(private QuickFinkok $quickFinkok)
    {
    }

    public function isProduction(): bool
    {
        return $this->quickFinkok->settings()->environment()->isProduction();
    }

    public function getUsername(): string
    {
        return $this->quickFinkok->settings()->username();
    }

    public function getPassword(): string
    {
        return $this->quickFinkok->settings()->password();
    }

    public function stamp(XmlContent $preCfdi): Cfdi
    {
        try {
            $result = $this->quickFinkok->stamp($preCfdi->getValue());
        } catch (Throwable $exception) {
            throw new ServiceException('Error on call Finkok stamp', $preCfdi, $exception);
        }

        if ('' === $result->xml()) {
            throw new StampException('Finkok stamp did not return the CFDI', $this->buildMessages($result));
        }

        if ('' === $result->uuid()) {
            throw new StampException('Finkok stamp did not return the UUID', $this->buildMessages($result));
        }

        return new Cfdi(
            new Uuid($result->uuid()),
            new XmlContent($result->xml()),
        );
    }

    private function buildMessages(StampingResult $result): StampErrors
    {
        $messages = [];
        foreach ($result->alerts() as $alert) {
            $messages[] = new StampError($alert->errorCode(), $alert->message());
        }
        return new StampErrors(...$messages);
    }
}
