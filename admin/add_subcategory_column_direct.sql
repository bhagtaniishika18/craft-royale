-- Direct SQL to add subcategory_id column
-- Run this in phpMyAdmin

-- Check if column exists and add it
SET @dbname = DATABASE();
SET @tablename = 'products';
SET @columnname = 'subcategory_id';

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT "Column already exists" as result',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' int(11) DEFAULT NULL AFTER category_id')
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index if it doesn't exist
SET @indexname = 'idx_subcategory_id';
SET @preparedStatement2 = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @indexname)
  ) > 0,
  'SELECT "Index already exists" as result',
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX ', @indexname, ' (subcategory_id)')
));

PREPARE indexIfNotExists FROM @preparedStatement2;
EXECUTE indexIfNotExists;
DEALLOCATE PREPARE indexIfNotExists;

SELECT "Done! subcategory_id column has been added." as result;

