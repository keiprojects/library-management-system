ALTER TABLE borrow_records
    MODIFY borrow_date DATETIME NOT NULL,
    MODIFY due_date DATETIME NOT NULL,
    MODIFY return_date DATETIME DEFAULT NULL;

ALTER TABLE reservation_cart_items
    ADD COLUMN due_date DATETIME NULL AFTER status;

UPDATE reservation_cart_items
SET due_date = DATE_ADD(created_at, INTERVAL 7 DAY)
WHERE due_date IS NULL;

ALTER TABLE reservation_cart_items
    MODIFY due_date DATETIME NOT NULL;
