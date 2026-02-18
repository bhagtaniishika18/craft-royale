-- Update review timestamps to show different time displays
-- This will make the timeAgo function show various times instead of "Just now"

-- Update first review to be "Just now" (0 seconds ago)
UPDATE reviews SET created_at = NOW() WHERE id = 1;

-- Update second review to be "2 hrs ago" (2 hours ago)
UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 2 HOUR) WHERE id = 2;

-- Update third review to be "Yesterday" (1 day ago)
UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE id = 3;

-- Update fourth review to be "2 days ago" (2 days ago)
UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 2 DAY) WHERE id = 4;

-- Update fifth review to be "1 week ago" (7 days ago)
UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 7 DAY) WHERE id = 5;

-- If you have more reviews, you can add more UPDATE statements
-- Example for 1 month ago:
-- UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 1 MONTH) WHERE id = 6;











