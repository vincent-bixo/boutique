<?php
/**
 * Holds the main administration screen controller of the module.
 *
 * PHP version 7.4
 *
 * @author    SendCloud Global B.V. <contact@sendcloud.eu>
 * @copyright 2023 SendCloud Global B.V.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 * @category  Shipping
 *
 * @see      https://sendcloud.eu
 */

use Sendcloud\PrestaShop\Classes\Bootstrap\Bootstrap;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AdminSupportController
 */
class AdminSupportController extends ModuleAdminController
{
    const QUEUE_ITEM_TABLE = 'sendcloud_queues';
    const PROCESS_TABLE = 'sendcloud_processes';

    /**
     * AdminSupportController constructor
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();

        Bootstrap::init();
    }

    /**
     * Handles initial GET request
     *
     * @return void
     * @throws PrestaShopDatabaseException
     */
    public function init()
    {
        $action = Tools::getValue('action');

        if ($action === 'fetchItems') {
            $this->fetchAllQueueItems();
        } elseif ($action === 'deleteItems') {
            $this->deleteQueueItemsByStatus();
        } elseif ($action === 'fetchProcesses') {
            $this->fetchProcesses();
        } elseif ($action === 'deleteProcesses') {
            $this->deleteProcesses();
        } else {
            die(json_encode(['error' => 'Invalid action specified.']));
        }
    }

    /**
     * Fetches queue items by given status (completed, in_progress, queued)
     *
     * @return void
     * @throws PrestaShopDatabaseException
     */
    public function fetchAllQueueItems()
    {
        $status = Tools::getValue('status') ?? 'queued';
        $limit = Tools::getValue('limit') ?? 100;

        $query = new DbQuery();
        $query->select('*')->from(self::QUEUE_ITEM_TABLE);
        $query->where(pSQL('status') . ' = "' . pSQL($status) . '"');
        $query->limit($limit);

        $records = Db::getInstance()->executeS($query);

        die(json_encode(['records' => $records, 'count' => count($records)]));
    }

    /**
     * Deletes queue items by given status
     *
     * @return void
     */
    public function deleteQueueItemsByStatus()
    {
        $status = Tools::getValue('status');

        $deleted = Db::getInstance()->delete(
            self::QUEUE_ITEM_TABLE,
            'status = "' . pSQL($status) . '"'
        );

        if ($deleted) {
            die(json_encode(['success' => true, 'message' => 'Records deleted successfully.']));
        } else {
            die(json_encode(['success' => false, 'message' => 'Failed to delete records.']));
        }
    }

    /**
     * Fetches processes
     *
     * @return void
     * @throws PrestaShopDatabaseException
     */
    public function fetchProcesses()
    {
        $limit = Tools::getValue('limit');

        $query = new DbQuery();
        $query->select('*')->from(self::PROCESS_TABLE);
        $query->limit($limit);

        $records = Db::getInstance()->executeS($query);

        die(json_encode(['records' => $records, 'count' => count($records)]));
    }

    /**
     * Deletes queue items by given status
     *
     * @return void
     */
    public function deleteProcesses()
    {
        $deleted = Db::getInstance()->delete(
            self::PROCESS_TABLE
        );

        if ($deleted) {
            die(json_encode(['success' => true, 'message' => 'Records deleted successfully.']));
        } else {
            die(json_encode(['success' => false, 'message' => 'Failed to delete records.']));
        }
    }
}
