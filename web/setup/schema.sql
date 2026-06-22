-- Forest Trove Database Schema
-- Run this script once to initialize the database.

CREATE DATABASE IF NOT EXISTS forest_trove CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forest_trove;

-- --------------------------------------------------------
-- Role
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS Role (
    ID   INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(50)  NOT NULL,
    Description VARCHAR(500)
);

INSERT INTO Role (Name, Description) VALUES
    ('Admin', 'Full administrative access: register, edit, and delete treasures.'),
    ('User',  'Standard user access: browse and claim treasures.');

-- --------------------------------------------------------
-- User
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS User (
    ID        INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50)  NOT NULL,
    LastName  VARCHAR(50)  NOT NULL,
    Username  VARCHAR(50)  NOT NULL UNIQUE,
    Password  VARCHAR(255) NOT NULL,   -- bcrypt hash
    RoleID    INT          NOT NULL DEFAULT 2,
    FOREIGN KEY (RoleID) REFERENCES Role(ID)
);

-- Default admin user: username = admin, password = ChangeMe123!
-- Generated with password_hash('ChangeMe123!', PASSWORD_BCRYPT)
INSERT INTO User (FirstName, LastName, Username, Password, RoleID) VALUES
    ('Admin', 'User', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
-- NOTE: The hash above is for the string 'password' (Laravel default).
-- Change this immediately: UPDATE User SET Password = password_hash_value WHERE Username = 'admin';

-- --------------------------------------------------------
-- Forest
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS Forest (
    ID         INT AUTO_INCREMENT PRIMARY KEY,
    Name       VARCHAR(50)  NOT NULL,
    GoogleLink VARCHAR(250),
    Latitude   FLOAT,
    Longitude  FLOAT
);

-- --------------------------------------------------------
-- Seeker  (person who found a treasure)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS Seeker (
    ID          INT AUTO_INCREMENT PRIMARY KEY,
    FirstName   VARCHAR(50)   NOT NULL,
    ImagePath   VARCHAR(250),
    Description VARCHAR(5000)
);

-- --------------------------------------------------------
-- Treasure
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS Treasure (
    ID          VARCHAR(50)   PRIMARY KEY,
    ImagePath   VARCHAR(250),
    Name        VARCHAR(250)  NOT NULL,
    Description VARCHAR(5000),
    ForestID    INT,
    Latitude    FLOAT,
    Longitude   FLOAT,
    CreatedDate DATE          NOT NULL DEFAULT (CURDATE()),
    PlacedDate  DATE,
    IsFound     BOOLEAN       NOT NULL DEFAULT FALSE,
    SeekerID    INT,
    FoundDate   DATE,
    FOREIGN KEY (ForestID)  REFERENCES Forest(ID),
    FOREIGN KEY (SeekerID)  REFERENCES Seeker(ID)
);
