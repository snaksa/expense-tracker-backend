<?php

namespace App\Tests\Feature;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use KunicMarko\GraphQLTest\Operation\Mutation;
use KunicMarko\GraphQLTest\Operation\Query;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class BaseTestCase extends WebTestCase
{
    use FixturesTrait {
        tearDown as protected fixturesTearDown;
    }

    const endpoint = '/';

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var KernelBrowser
     */
    protected $client;

    /**
     * @var mixed[]
     */
    protected $initializedReferences;

    public function getReferences()
    {
        if ($this->initializedReferences === null) {
            foreach ($this->fixtures->getReferences() as $index => $object) {
                $this->initializedReferences[$index] = $this->fixtures->getReference($index);
            }
        }

        return $this->initializedReferences;
    }

    /**
     * @param callable $callback
     * @return array
     */
    public function filterFixtures(callable $callback)
    {
        $values = [];
        foreach ($this->getReferences() as $index => $reference) {
            if ($callback($reference, $index)) {
                $values[$reference->getId()] = $reference;
            }
        }

        return array_values($values);
    }

    /**
     * @param string $queryName
     * @param array $data
     * @param array $fields
     * @param array $files
     *
     * @return Crawler
     */
    public function query(string $queryName, array $data, array $fields, array $files = []): Crawler
    {
        $query = new Query(
            $queryName,
            $data,
            $fields
        );
        return $this->client->request(
            'POST',
            self::endpoint,
            ['query' => $query()],
            $files
        );
    }

    /**
     * @param string $mutationName
     * @param array $data
     * @param array $fields
     * @param array $files
     * @param string $apiKey
     *
     * @return Crawler
     */
    public function mutation(string $mutationName, array $data, array $fields, array $files = []): Crawler
    {
        $mutation = new Mutation(
            $mutationName,
            $data,
            $fields
        );

        return $this->client->request(
            'POST',
            self::endpoint,
            ['query' => $mutation()],
            $files
        );
    }

    /**
     * @param Response $response
     */
    public function assertOk(Response $response): void
    {
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    public function getResponseContent(Response $response): array
    {
        $content = json_decode($response->getContent(), true);

        if (!isset($content['data']) || (isset($content['errors']) && isset($content['data']))) {
            if (isset($content['errors'])) {
                $appendError = function(string $currentErrors, string $newError) {
                    if (strlen($currentErrors) > 1) {
                        $currentErrors = $currentErrors . ' | ';
                    }

                    return $currentErrors . $newError;
                };

                $error = "";
                foreach ($content['errors'] as $errorData) {
                    if (isset($errorData['state']['input'])) {
                        foreach ($errorData['state']['input'] as $input) {
                            $inputError = $input['message'] ?? 'Unknown input error';
                            $error = $appendError($error, $inputError);
                        }
                    } else {
                        if (isset($errorData['debugMessage'])) {
                            $generalError = $errorData['debugMessage'];
                        } else {
                            $generalError = $errorData['message'] ?? 'Unknown general error';
                        }
                        $error = $appendError($error, $generalError);
                    }
                }
            } else {
                $error = 'No data returned for test';
            }


            $this->assertTrue(false, $error);
        }

        return $content['data'];
    }

    public function assertHasError(string $error, Response $response)
    {
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($error, $content['errors'][0]['message']);
    }

    public function assertInputHasError(string $error, Response $response)
    {
        $this->assertHasTypeError('input', $error, $response);
    }

    private function assertHasTypeError($type, string $error, Response $response)
    {
        $exists = false;
        $content = json_decode($response->getContent(), true);

        $message = 'No input error messages found';
        if (isset($content['errors'])) {
            $message = 'Has error messages, but no input errors';
            foreach ($content['errors'] as $errorData) {
                $message = 'Input error doesn\'t match';
                if (isset($errorData['state'][$type])) {
                    foreach ($errorData['state'][$type] as $input) {
                        if ($input['message'] === $error) {
                            $exists = true;
                        }
                    }
                }
            }
        }

        $this->assertTrue($exists, $message);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->fixtures && $this->fixtures->getManager()) {
            $this->fixtures->getManager()->clear();
            $this->fixtures = null;
        }

        $this->fixturesTearDown();

        $this->initializedReferences = null;
    }

}
