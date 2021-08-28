<?php

declare(strict_types=1);

namespace Dufrei\ApiJsonCfdiBridge\Tests\Unit\StampService;

use Dufrei\ApiJsonCfdiBridge\StampService\FinkokStampService;
use Dufrei\ApiJsonCfdiBridge\StampService\StampException;
use Dufrei\ApiJsonCfdiBridge\Tests\TestCase;
use Dufrei\ApiJsonCfdiBridge\Values\Cfdi;
use Dufrei\ApiJsonCfdiBridge\Values\Uuid;
use Dufrei\ApiJsonCfdiBridge\Values\XmlContent;
use Exception;
use PhpCfdi\Finkok\QuickFinkok;
use PhpCfdi\Finkok\Services\Stamping\StampingResult;
use PHPUnit\Framework\MockObject\MockObject;

final class FinkokStampServiceTest extends TestCase
{
    public function testStampWithValidResponse(): void
    {
        $precfdi = new XmlContent($this->fileContents('signed.xml'));
        $cfdi = new Cfdi(
            new Uuid('CEE4BE01-ADFA-4DEB-8421-ADD60F0BEDAC'),
            new XmlContent($this->fileContents('stamped.xml')),
        );
        $stampingResult = new StampingResult('result', (object) [
            'result' => (object) [
                'xml' => $cfdi->getXml()->getValue(),
                'UUID' => $cfdi->getUuid()->getValue(),
            ],
        ]);

        /** @var QuickFinkok&MockObject $quickFinkok */
        $quickFinkok = $this->createMock(QuickFinkok::class);
        $quickFinkok->expects($this->once())
            ->method('stamp')
            ->with($precfdi->getValue())
            ->willReturn($stampingResult);

        $service = new FinkokStampService($quickFinkok);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($cfdi, $service->stamp($precfdi));
    }

    public function testStampCatchesFinkokError(): void
    {
        $remoteException = new Exception('oh no, oh no, oh no no no no no');

        /** @var QuickFinkok&MockObject $quickFinkok */
        $quickFinkok = $this->createMock(QuickFinkok::class);
        $quickFinkok->expects($this->once())
            ->method('stamp')
            ->willThrowException($remoteException);

        $service = new FinkokStampService($quickFinkok);

        /** @var StampException $cachedException */
        $cachedException = $this->catchException(
            fn (): Cfdi => $service->stamp(new XmlContent('<xml/>')),
            StampException::class,
            'Finkok::stamp didn\'t create the exception'
        );

        $this->assertSame('Finkok stamp error', $cachedException->getMessage());
        $this->assertSame($remoteException, $cachedException->getPrevious());
        $this->assertSame(
            ["[ERROR] {$remoteException->getMessage()}"],
            $cachedException->getErrors()->messages()
        );
    }

    public function testStampCatchesWithErrors(): void
    {
        $stampingResult = new StampingResult('result', (object) [
            'result' => (object) array_filter([
                'Incidencias' => (object) [
                    'Incidencia' => [
                        (object) ['CodigoError' => 'E01', 'MensajeIncidencia' => 'Error 1'],
                        (object) ['CodigoError' => 'E02', 'MensajeIncidencia' => 'Error 2'],
                    ],
                ],
            ]),
        ]);
        /** @var QuickFinkok&MockObject $quickFinkok */
        $quickFinkok = $this->createMock(QuickFinkok::class);
        $quickFinkok->expects($this->once())
            ->method('stamp')
            ->willReturn($stampingResult);

        $service = new FinkokStampService($quickFinkok);

        /** @var StampException $cachedException */
        $cachedException = $this->catchException(
            fn (): Cfdi => $service->stamp(new XmlContent('<xml/>')),
            StampException::class,
            'Finkok::stamp didn\'t create the exception'
        );

        $this->assertSame('Finkok stamp did not return the CFDI', $cachedException->getMessage());
        $this->assertNull($cachedException->getPrevious());
        $expectedErrors = [
            '[E01] Error 1',
            '[E02] Error 2',
        ];
        $this->assertSame($expectedErrors, $cachedException->getErrors()->messages());
    }

    /**
     * @param bool $withXml
     * @param bool $withUuid
     * @testWith [false, false]
     *           [true, false]
     *           [false, true]
     */
    public function testStampWithIncompleteResponse(bool $withXml, bool $withUuid): void
    {
        $precfdi = new XmlContent($this->fileContents('signed.xml'));
        $cfdi = new Cfdi(
            new Uuid('CEE4BE01-ADFA-4DEB-8421-ADD60F0BEDAC'),
            new XmlContent($this->fileContents('stamped.xml')),
        );
        $stampingResult = new StampingResult('result', (object) [
            'result' => (object) array_filter([
                'xml' => ($withXml) ? $cfdi->getXml()->getValue() : null,
                'UUID' => ($withUuid) ? $cfdi->getUuid()->getValue() : null,
            ]),
        ]);

        /** @var QuickFinkok&MockObject $quickFinkok */
        $quickFinkok = $this->createMock(QuickFinkok::class);
        $quickFinkok->expects($this->once())
            ->method('stamp')
            ->with($precfdi->getValue())
            ->willReturn($stampingResult);

        $service = new FinkokStampService($quickFinkok);
        $this->expectException(StampException::class);
        $this->expectExceptionMessage(sprintf('Finkok stamp did not return the %s', (! $withXml) ? 'CFDI' : 'UUID'));
        $this->assertEquals($cfdi, $service->stamp($precfdi));
    }
}
