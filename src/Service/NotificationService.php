<?php

namespace CommonGateway\NotificationsBundle\Service;

use CommonGateway\CoreBundle\Service\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
 * An example service for adding business logic to your class.
 *
 * @author Conduction.nl <info@conduction.nl>
 *
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 *
 * @category Service
 */
class NotificationService
{

    /**
     * @var array
     */
    private array $configuration;

    /**
     * @var array
     */
    private array $data;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * The plugin logger.
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    private CacheService $cacheService;


    /**
     * @param EntityManagerInterface $entityManager The Entity Manager.
     * @param LoggerInterface        $pluginLogger  The plugin version of the logger interface.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $pluginLogger,
        CacheService $cacheService
    ) {
        $this->entityManager = $entityManager;
        $this->logger        = $pluginLogger;
        $this->configuration = [];
        $this->data          = [];
        $this->cacheService  = $cacheService;
        $this->client        = new Client();

    }//end __construct()


    /**
     * An example handler that is triggered by an action.
     *
     * @param array $data          The data array
     * @param array $configuration The configuration array
     *
     * @return array A handler must ALWAYS return an array
     */
    public function notificationHandler(array $data, array $configuration): array
    {
        $this->data          = $data;
        $this->configuration = $configuration;

        $object = $this->data['object']->toArray();

        $memberships = $this->cacheService->searchObjects('', ['kanalen' => ['$elemMatch' => ['naam' => $object['kanaal']]]], ['https://zgw.opencatalogi.nl/schema/nrc.abonnement.schema.json'])['results'];

        $memberships = \Safe\json_decode(\Safe\json_encode($memberships), true);

        foreach ($memberships as $membership) {
            $this->client->post($membership['callbackUrl'], ['headers' => ['Authorization' => $membership['auth'], 'Content-Type' => 'application/json'], ['body' => \Safe\json_encode($object)]]);
        }


        return $data;


    }//end notificationHandler()


}//end class
