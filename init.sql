SET PASSWORD FOR 'root'@'localhost' = PASSWORD('rootpass');
FLUSH PRIVILEGES;

CREATE DATABASE IF NOT EXISTS my_db;
USE my_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100),
  pass VARCHAR(100),
  is_paid TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  title VARCHAR(100),
  description VARCHAR(100),
  status INT,
  created_at DATE,
  updated_at DATE,
  resolved_at DATE
);

CREATE INDEX IF NOT EXISTS idx_title ON tasks(title);


CREATE TABLE IF NOT EXISTS task_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_id INT NOT NULL,
  old_status INT,
  new_status INT,
  changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  razorpay_order_id VARCHAR(100) NOT NULL,
  razorpay_payment_id VARCHAR(100) DEFAULT NULL, -- filled after payment success
  amount INT NOT NULL,
  status ENUM('created', 'paid', 'failed') DEFAULT 'created',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);




-- Insert dummy data
DELIMITER $$

DROP PROCEDURE IF EXISTS populate_tasks $$
CREATE PROCEDURE populate_tasks()
BEGIN
  DECLARE i INT DEFAULT 1;
  DECLARE titleVal VARCHAR(100);
  DECLARE descVal VARCHAR(255);
  DECLARE statusVal INT;
  DECLARE created DATE;
  DECLARE updated DATE;
  DECLARE resolved DATE;

  DECLARE titles TEXT;
  DECLARE descriptions TEXT;

  -- Arrays as comma-separated values
  SET titles = 'Fix login bug,Design new logo,Write user documentation,Update database schema,Conduct code review,Optimize queries,Implement authentication,Deploy to staging,Write unit tests,Plan sprint backlog,Research new framework,Fix security vulnerability,Redesign homepage,Create API documentation,Implement search feature,Review analytics data,Refactor old code,Update dependencies,Resolve merge conflict,Test mobile responsiveness';
  
  SET descriptions = 'Ensure login error no longer occurs,Create a clean and modern logo,Document all features for end users,Modify tables to support new data types,Check PRs and leave comments,Improve DB response time by indexing,Enable JWT-based authentication,Deploy latest version for QA,Add coverage for all modules,Prepare tasks for next sprint,Look into alternatives to current stack,Patch XSS issue in frontend,Improve UX of main page,Detail every API endpoint,Add filter and ranking logic,Interpret recent traffic spikes,Clean up legacy methods,Upgrade all libraries to latest version,Fix code after branch merge,Ensure UI works well on all devices';

  WHILE i <= 100 DO
    -- Pick random title and description
    SET titleVal = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(titles, ',', FLOOR(1 + RAND() * 20)), ',', -1));
    SET descVal = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(descriptions, ',', FLOOR(1 + RAND() * 20)), ',', -1));
    
    SET statusVal = FLOOR(RAND() * 3); -- 0, 1, or 2
    SET created = DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY);
    SET updated = DATE_ADD(created, INTERVAL FLOOR(RAND() * 5) DAY);
    SET resolved = IF(statusVal = 2, DATE_ADD(updated, INTERVAL FLOOR(RAND() * 3) DAY), NULL);

    INSERT INTO tasks (user_id, title, description, status, created_at, updated_at, resolved_at)
    VALUES (1, titleVal, descVal, statusVal, created, updated, resolved);

    SET i = i + 1;
  END WHILE;
END $$

CALL populate_tasks();
DROP PROCEDURE IF EXISTS populate_tasks $$

DELIMITER ;
