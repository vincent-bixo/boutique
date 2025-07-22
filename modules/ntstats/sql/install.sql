CREATE TABLE IF NOT EXISTS `PREFIX_nts_config` (
    `id_nts_config`                     int(10)         unsigned    NOT NULL    auto_increment,
    `id_shop`                           int(10)         unsigned    NOT NULL,
    `id_shop_group`                     int(10)         unsigned    NOT NULL,
    `nb_combinations_min_without_stock` int(10)         unsigned    NOT NULL    DEFAULT "1",
    `amount_customer_min_one_order`     int(10)         unsigned    NOT NULL    DEFAULT "100",
    `amount_customer_min_orders`        int(10)         unsigned    NOT NULL    DEFAULT "50",
    `group_product_reference`           tinyint(1)                  NOT NULL    DEFAULT "1",
    `autoload`                          tinyint(1)                  NOT NULL    DEFAULT "1",
    `receive_email_version`             tinyint(1)                  NOT NULL    DEFAULT "0",
    `mail_version`                      TEXT                        NOT NULL,
    `automation_2nt_ip`                 int(10)         unsigned    NOT NULL    DEFAULT "0",
    `last_shop_url`                     TEXT                        NOT NULL,
    `automation_2nt`                    tinyint(1)      unsigned    NOT NULL DEFAULT "0",
    `automation_2nt_hours`              int(10)         unsigned    NOT NULL DEFAULT "0",
    `automation_2nt_minutes`            int(10)         unsigned    NOT NULL DEFAULT "0",
    `mail_stock_alert`                  TEXT                        NOT NULL,
    `default_period`                    INT                         NOT NULL DEFAULT "1",
    `email_alert_threshold`             int(10)         unsigned    NOT NULL DEFAULT "3",
    `email_alert_type`                  int(10)         unsigned    NOT NULL DEFAULT "0",
    `email_alert_active`                int(10)                     NOT NULL DEFAULT "1",
    `email_alert_send_empty`            tinyint(1)      unsigned    NOT NULL DEFAULT "0",
    `dashboard_sales`                   tinyint(1)      unsigned    NOT NULL DEFAULT "1",
    `dashboard_nb_orders`               tinyint(1)      unsigned    NOT NULL DEFAULT "1",
    `increase_server_timeout`           tinyint(1)      unsigned    NOT NULL DEFAULT "0",
    `server_timeout_value`              int(10)                     NOT NULL DEFAULT "6000",
    `increase_server_memory`            tinyint(1)      unsigned    NOT NULL DEFAULT "0",
    `server_memory_value`               int(10)                     NOT NULL DEFAULT "128",
    `order_type_date`                   int(10)                     NOT NULL DEFAULT "1",
    `order_date_state`                  int(10)                     NOT NULL DEFAULT "0",
    `order_type_location`               int(10)                     NOT NULL DEFAULT "1",
    `return_valid_states`               TEXT                        NOT NULL,
    `date_add`                          datetime,
    `date_upd`                          datetime,
    PRIMARY KEY (`id_nts_config`)
) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_nts_config_payment_method` (
    `id_nts_config_payment_method`  int(10)         unsigned    NOT NULL    auto_increment,
    `id_nts_config`                 int(10)         unsigned    NOT NULL,
    `payment_method`                TEXT                        NOT NULL,
    `display_name`                  TEXT                        NOT NULL,
    `date_add`                      datetime,
    `date_upd`                      datetime,
    PRIMARY KEY (`id_nts_config_payment_method`)
) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_nts_config_profil_countries` (
    `id_nts_config_profil_countries`    int(10)         unsigned    NOT NULL    auto_increment,
    `id_nts_config`                     int(10)         unsigned    NOT NULL,
    `id_profil`                         int(10)         unsigned    NOT NULL,
    `id_countries`                      TEXT                        NOT NULL,
    `date_add`                          datetime,
    `date_upd`                          datetime,
    PRIMARY KEY (`id_nts_config_profil_countries`)
) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `PREFIX_nts_tables_config` (
    `id_nts_tables_config`  int(10)         unsigned    NOT NULL    auto_increment,
    `name`                  TEXT                        NOT NULL,
    `config`                TEXT                        NOT NULL,
    `date_add`              datetime,
    `date_upd`              datetime,
    PRIMARY KEY (`id_nts_tables_config`)
) ENGINE=ENGINE_TYPE  DEFAULT CHARSET=utf8;
