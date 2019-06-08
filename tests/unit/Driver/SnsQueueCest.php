<?php declare(strict_types=1);

namespace Tests\Unit\Driver;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Codeception\Example;
use Initx\Driver\SqsQueue;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
use Mockery;
use Tests\Double\EnvelopeMother;
use Tests\UnitTester;

class SnsQueueCest
{
    public function addOk(UnitTester $I)
    {
        $envelope = EnvelopeMother::any();
        $client = Mockery::mock(SqsClient::class);
        $client->expects('getQueueUrl')->andReturn(new Result(['QueueUrl' => 'some_url']));
        $client->expects('sendMessage')->andReturn(new Result());
        $queue = new SqsQueue($client, 'name');

        $result = $queue->add($envelope);

        $I->assertTrue($result);
    }

    public function addThrows(UnitTester $I)
    {
        $envelope = EnvelopeMother::any();
        $client = Mockery::mock(SqsClient::class);
        $client->expects('getQueueUrl')->andReturn(new Result(['QueueUrl' => 'some_url']));
        $client->expects('sendMessage')->andReturn(false);
        $queue = new SqsQueue($client, 'name.fifo');

        $I->expectException(IllegalStateException::class, function () use ($queue, $envelope) {
            $queue->add($envelope);
        });
    }

    /**
     * @example { "method": "remove" }
     * @example { "method": "poll" }
     * @example { "method": "peek" }
     * @example { "method": "element" }
     */
    public function queryMethod(UnitTester $I, Example $example)
    {
        $client = Mockery::mock(SqsClient::class);
        $client->expects('getQueueUrl')
            ->once()
            ->andReturn(new Result(['QueueUrl' => 'some_url']));
        $client->expects('receiveMessage')
            ->once()
            ->andReturn(new Result([
                'Messages' => [
                    [
                        'Body' => $this->messageDouble(),
                        'ReceiptHandle' => 'handle',
                    ]
                ]
            ]));
        $client->expects('deleteMessage')->once();
        $queue = new SqsQueue($client, 'name.fifo');
        $method = $example['method'];

        $element = $queue->$method();

        $I->assertSame('Turner, Cremin and Streich', $element->getTitle());
        $I->assertNotEmpty($element->getPayload());
        $I->assertNotEmpty($element->getTimestamp());
    }

    /**
     * @example { "method": "remove" }
     * @example { "method": "element" }
     */
    public function removeAndElementThrows(UnitTester $I, Example $example)
    {
        $client = Mockery::mock(SqsClient::class);
        $client->expects('getQueueUrl')
            ->once()
            ->andReturn(new Result(['QueueUrl' => 'some_url']));
        $client->expects('receiveMessage')
            ->once()
            ->andReturn(new Result(['Messages' => []]));
        $client->expects('deleteMessage')->once();
        $queue = new SqsQueue($client, 'name.fifo');
        $method = $example['method'];

        $I->expectException(NoSuchElementException::class, function () use ($queue, $method) {
            $queue->$method();
        });
    }

    private function messageDouble(): string
    {
        return '{"title":"Turner, Cremin and Streich","payload":"Qui excepturi praesentium consequatur quaerat quo' .
            'totam rem. Quam et aut dicta ut totam. Veritatis omnis quis consectetur.","timestamp":"2018-12-05T08:' .
            '03:05+01:00"}';
    }
}
