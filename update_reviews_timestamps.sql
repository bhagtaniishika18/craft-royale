-- Update reviews timestamps to show different times
-- This will make reviews appear at different intervals

-- Update first review: Keep current time (Just now)
-- UPDATE reviews SET created_at = NOW() WHERE id = (SELECT id FROM reviews WHERE status='approved' ORDER BY id LIMIT 1);

-- Update second review: 2 hours ago
UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 2 HOUR) WHERE id = (SELECT id FROM (SELECT id FROM reviews WHERE status='approved' ORDER BY id LIMIT 1 OFFSET 1) AS temp);

-- Update third review: 1 day ago (Yesterday)
UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE id = (SELECT id FROM (SELECT id FROM reviews WHERE status='approved' ORDER BY id LIMIT 1 OFFSET 2) AS temp);

-- Update fourth review: 2 days ago
UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 2 DAY) WHERE id = (SELECT id FROM (SELECT id FROM reviews WHERE status='approved' ORDER BY id LIMIT 1 OFFSET 3) AS temp);

-- Alternative: Update all approved reviews with different timestamps
-- UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 0 HOUR) WHERE status='approved' AND id % 4 = 1;
-- UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 2 HOUR) WHERE status='approved' AND id % 4 = 2;
-- UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE status='approved' AND id % 4 = 3;
-- UPDATE reviews SET created_at = DATE_SUB(NOW(), INTERVAL 2 DAY) WHERE status='approved' AND id % 4 = 0;











