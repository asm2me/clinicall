-- ClinicAll v2 — PostgreSQL Schema (standalone multi-tenant)

-- ── Tenants ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tenants (
    id            UUID        PRIMARY KEY DEFAULT gen_random_uuid(),
    name          VARCHAR(255) NOT NULL,
    slug          VARCHAR(100) NOT NULL UNIQUE,
    custom_domain VARCHAR(255),
    plan          VARCHAR(50)  NOT NULL DEFAULT 'standard',
    enabled       BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at    TIMESTAMP    NOT NULL DEFAULT NOW(),
    updated_at    TIMESTAMP    NOT NULL DEFAULT NOW()
);

-- ── Users ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id   UUID         REFERENCES tenants(id) ON DELETE CASCADE,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        VARCHAR(20)  NOT NULL DEFAULT 'staff',  -- superadmin|admin|staff|doctor
    doctor_id   UUID,
    enabled     BOOLEAN      NOT NULL DEFAULT TRUE,
    last_login  TIMESTAMP,
    created_at  TIMESTAMP    NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP    NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_users_tenant ON users(tenant_id);
CREATE INDEX IF NOT EXISTS idx_users_email  ON users(email);

-- ── Clinics ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS clinics (
    id          UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id   UUID         NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    name        VARCHAR(255) NOT NULL,
    description TEXT,
    address     TEXT,
    phone       VARCHAR(50),
    email       VARCHAR(255),
    timezone    VARCHAR(100) NOT NULL DEFAULT 'UTC',
    enabled     BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP    NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP    NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_clinics_tenant ON clinics(tenant_id);

-- ── Doctors ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS doctors (
    id          UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id   UUID         NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    clinic_id   UUID         NOT NULL REFERENCES clinics(id) ON DELETE CASCADE,
    name        VARCHAR(255) NOT NULL,
    specialty   VARCHAR(255),
    phone       VARCHAR(50),
    email       VARCHAR(255),
    bio         TEXT,
    enabled     BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMP    NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP    NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_doctors_tenant ON doctors(tenant_id);
CREATE INDEX IF NOT EXISTS idx_doctors_clinic ON doctors(clinic_id);

-- Add FK from users to doctors (after both tables exist)
ALTER TABLE users ADD CONSTRAINT fk_users_doctor
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
    NOT VALID;

-- ── Doctor Schedules ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS doctor_schedules (
    id               UUID     PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id        UUID     NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    doctor_id        UUID     NOT NULL REFERENCES doctors(id) ON DELETE CASCADE,
    day_of_week      SMALLINT NOT NULL CHECK (day_of_week BETWEEN 0 AND 6),
    start_time       TIME     NOT NULL,
    end_time         TIME     NOT NULL,
    slot_duration    SMALLINT NOT NULL DEFAULT 15,
    max_appointments SMALLINT NOT NULL DEFAULT 1,
    enabled          BOOLEAN  NOT NULL DEFAULT TRUE
);

CREATE INDEX IF NOT EXISTS idx_schedules_doctor ON doctor_schedules(doctor_id, day_of_week);

-- ── Schedule Exceptions ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS schedule_exceptions (
    id             UUID     PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id      UUID     NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    doctor_id      UUID     NOT NULL REFERENCES doctors(id) ON DELETE CASCADE,
    exception_date DATE     NOT NULL,
    exception_type VARCHAR(20) NOT NULL DEFAULT 'off',
    start_time     TIME,
    end_time       TIME,
    note           VARCHAR(500),
    created_at     TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_exceptions_doctor ON schedule_exceptions(doctor_id, exception_date);

-- ── Appointments ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS appointments (
    id               UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id        UUID         NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    clinic_id        UUID         NOT NULL REFERENCES clinics(id)  ON DELETE RESTRICT,
    doctor_id        UUID         NOT NULL REFERENCES doctors(id)  ON DELETE RESTRICT,
    patient_name     VARCHAR(255) NOT NULL,
    patient_phone    VARCHAR(50)  NOT NULL,
    patient_email    VARCHAR(255),
    patient_dob      DATE,
    patient_notes    TEXT,
    appointment_date DATE         NOT NULL,
    appointment_time TIME         NOT NULL,
    duration         SMALLINT     NOT NULL DEFAULT 15,
    status           VARCHAR(20)  NOT NULL DEFAULT 'pending',
    type             VARCHAR(100) NOT NULL DEFAULT 'general',
    staff_notes      TEXT,
    reminder_sent    BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at       TIMESTAMP    NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMP    NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_appts_tenant  ON appointments(tenant_id);
CREATE INDEX IF NOT EXISTS idx_appts_doctor  ON appointments(doctor_id, appointment_date);
CREATE INDEX IF NOT EXISTS idx_appts_date    ON appointments(appointment_date, appointment_time);
CREATE INDEX IF NOT EXISTS idx_appts_status  ON appointments(status);
CREATE INDEX IF NOT EXISTS idx_appts_phone   ON appointments(patient_phone);
