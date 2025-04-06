-- Add settings columns to users table
ALTER TABLE users
ADD COLUMN email_notifications TINYINT(1) DEFAULT 1,
ADD COLUMN campaign_notifications TINYINT(1) DEFAULT 1,
ADD COLUMN subscriber_notifications TINYINT(1) DEFAULT 1,
ADD COLUMN dark_mode TINYINT(1) DEFAULT 0,
ADD COLUMN timezone VARCHAR(50) DEFAULT 'UTC'; 