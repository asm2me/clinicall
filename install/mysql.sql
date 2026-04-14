-- ClinicAll v2 — MySQL / MariaDB Schema (standalone multi-tenant)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `tenants` (
    `id`            CHAR(36)     NOT NULL,
    `name`          VARCHAR(255) NOT NULL,
    `slug`          VARCHAR(100) NOT NULL,
    `custom_domain` VARCHAR(255) DEFAULT NULL,
    `plan`          VARCHAR(50)  NOT NULL DEFAULT 'standard',
    `enabled`       TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tenants_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
    `id`          CHAR(36)     NOT NULL,
    `tenant_id`   CHAR(36)     DEFAULT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `email`       VARCHAR(255) NOT NULL,
    `password`    VARCHAR(255) NOT NULL,
    `role`        VARCHAR(20)  NOT NULL DEFAULT 'staff',
    `doctor_id`   CHAR(36)     DEFAULT NULL,
    `enabled`     TINYINT(1)   NOT NULL DEFAULT 1,
    `last_login`  DATETIME     DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    KEY `idx_users_tenant` (`tenant_id`),
    CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clinics` (
    `id`          CHAR(36)     NOT NULL,
    `tenant_id`   CHAR(36)     NOT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `description` TEXT         DEFAULT NULL,
    `address`     TEXT         DEFAULT NULL,
    `phone`       VARCHAR(50)  DEFAULT NULL,
    `email`       VARCHAR(255) DEFAULT NULL,
    `timezone`    VARCHAR(100) NOT NULL DEFAULT 'UTC',
    `enabled`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_clinics_tenant` (`tenant_id`),
    CONSTRAINT `fk_clinics_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `doctors` (
    `id`          CHAR(36)     NOT NULL,
    `tenant_id`   CHAR(36)     NOT NULL,
    `clinic_id`   CHAR(36)     NOT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `specialty`   VARCHAR(255) DEFAULT NULL,
    `phone`       VARCHAR(50)  DEFAULT NULL,
    `email`       VARCHAR(255) DEFAULT NULL,
    `bio`         TEXT         DEFAULT NULL,
    `enabled`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_doctors_tenant` (`tenant_id`),
    KEY `idx_doctors_clinic` (`clinic_id`),
    CONSTRAINT `fk_doctors_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_doctors_clinic` FOREIGN KEY (`clinic_id`) REFERENCES `clinics`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `users`
    ADD CONSTRAINT `fk_users_doctor`
    FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `doctor_schedules` (
    `id`               CHAR(36)  NOT NULL,
    `tenant_id`        CHAR(36)  NOT NULL,
    `doctor_id`        CHAR(36)  NOT NULL,
    `day_of_week`      TINYINT   NOT NULL,
    `start_time`       TIME      NOT NULL,
    `end_time`         TIME      NOT NULL,
    `slot_duration`    SMALLINT  NOT NULL DEFAULT 15,
    `max_appointments` SMALLINT  NOT NULL DEFAULT 1,
    `enabled`          TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_schedules_doctor` (`doctor_id`, `day_of_week`),
    CONSTRAINT `fk_schedules_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_schedules_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `schedule_exceptions` (
    `id`             CHAR(36)     NOT NULL,
    `tenant_id`      CHAR(36)     NOT NULL,
    `doctor_id`      CHAR(36)     NOT NULL,
    `exception_date` DATE         NOT NULL,
    `exception_type` VARCHAR(20)  NOT NULL DEFAULT 'off',
    `start_time`     TIME         DEFAULT NULL,
    `end_time`       TIME         DEFAULT NULL,
    `note`           VARCHAR(500) DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_exceptions_doctor` (`doctor_id`, `exception_date`),
    CONSTRAINT `fk_exceptions_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exceptions_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `appointments` (
    `id`               CHAR(36)     NOT NULL,
    `tenant_id`        CHAR(36)     NOT NULL,
    `clinic_id`        CHAR(36)     NOT NULL,
    `doctor_id`        CHAR(36)     NOT NULL,
    `patient_name`     VARCHAR(255) NOT NULL,
    `patient_phone`    VARCHAR(50)  NOT NULL,
    `patient_email`    VARCHAR(255) DEFAULT NULL,
    `patient_dob`      DATE         DEFAULT NULL,
    `patient_notes`    TEXT         DEFAULT NULL,
    `appointment_date` DATE         NOT NULL,
    `appointment_time` TIME         NOT NULL,
    `duration`         SMALLINT     NOT NULL DEFAULT 15,
    `status`           VARCHAR(20)  NOT NULL DEFAULT 'pending',
    `type`             VARCHAR(100) NOT NULL DEFAULT 'general',
    `staff_notes`      TEXT         DEFAULT NULL,
    `reminder_sent`    TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_appts_tenant`  (`tenant_id`),
    KEY `idx_appts_doctor`  (`doctor_id`, `appointment_date`),
    KEY `idx_appts_date`    (`appointment_date`, `appointment_time`),
    KEY `idx_appts_status`  (`status`),
    KEY `idx_appts_phone`   (`patient_phone`),
    CONSTRAINT `fk_appts_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_appts_clinic` FOREIGN KEY (`clinic_id`) REFERENCES `clinics`  (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_appts_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors`  (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
