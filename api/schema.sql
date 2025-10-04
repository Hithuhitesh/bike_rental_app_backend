CREATE DATABASE IF NOT EXISTS bikerental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bikerental;

CREATE TABLE IF NOT EXISTS users (
  id VARCHAR(64) PRIMARY KEY,
  email VARCHAR(191) UNIQUE,
  name VARCHAR(191),
  phone VARCHAR(64),
  role VARCHAR(32) DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bikes (
  id VARCHAR(64) PRIMARY KEY,
  owner_id VARCHAR(191),
  name VARCHAR(191),
  type VARCHAR(64),
  model VARCHAR(128),
  location VARCHAR(191),
  city VARCHAR(128),
  price_hour INT DEFAULT 0,
  price_day INT DEFAULT 0,
  registration_number VARCHAR(64),
  availability_status VARCHAR(32) DEFAULT 'Inactive',
  verification_status VARCHAR(32) DEFAULT 'Pending',
  image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_bikes_owner ON bikes(owner_id);
CREATE INDEX idx_bikes_loc ON bikes(location);
CREATE INDEX idx_bikes_status ON bikes(availability_status, verification_status);

CREATE TABLE IF NOT EXISTS bookings (
  id VARCHAR(64) PRIMARY KEY,
  user_id VARCHAR(64),
  bike_id VARCHAR(64),
  start_time DATETIME,
  end_time DATETIME,
  pricing_mode ENUM('hourly','daily') DEFAULT 'hourly',
  total_amount INT DEFAULT 0,
  status VARCHAR(32) DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_book_bike ON bookings(bike_id);

INSERT INTO bikes (id, owner_id, name, type, location, price_hour, price_day, registration_number, availability_status, verification_status, image_url, created_at, model, city)
VALUES
  ('seed1','admin@demo.com','Honda Activa 6G','Scooter','Adyar',45,360,'TN00AA1111','Available','Approved','',NOW(),'Activa 6G','Chennai'),
  ('seed2','admin@demo.com','KTM RC 390','Sports','Velachery',90,720,'TN00AA2222','Available','Approved','',NOW(),'RC 390','Chennai'),
  ('seed3','admin@demo.com','Royal Enfield Classic 350','Classic','Mylapore',70,560,'TN00AA3333','Inactive','Pending','',NOW(),'Classic 350','Chennai');
