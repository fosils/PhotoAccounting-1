-- --To CREATE the database :
-- sudo -u postgres createdb photo_accounting

-- -- To execute this script within a PostgreSQL session (adjust path accordingly) :
-- \i /home/pmg/Documents/photo_accounting/www/PhotoAccounting/sql/photo_accounting.sql

DROP VIEW IF EXISTS rnum_next;
DROP TABLE IF EXISTS receipts;
CREATE TABLE receipts (	id SERIAL PRIMARY KEY, 
						customer_id INT, 
						receipt_number SERIAL,
						image_name CHARACTER VARYING(255), 
						entry_date DATE DEFAULT current_date, 
						text VARCHAR(9999), 
						amount NUMERIC(20, 2) DEFAULT 0.00, 
						account INT, 
						vat_code VARCHAR(10),
						offset_account INT, 
						s3url VARCHAR(300), 
						received_date TIMESTAMP);

INSERT INTO receipts (customer_id, image_name, entry_date, text, amount, account, offset_account) VALUES (1,'','2012-08-14','TEST 1',1,1,1);
INSERT INTO receipts (customer_id, image_name, entry_date, text, amount, account, offset_account) VALUES (1,'','2012-08-14','TEST 2',2,2,2);
INSERT INTO receipts (customer_id, image_name, entry_date, text, amount, account, offset_account) VALUES (1,'','2012-08-14','TEST 4',4,4,4);
INSERT INTO receipts (customer_id, image_name, entry_date, text, amount, account, offset_account) VALUES (1,'','2012-08-14','TEST 5',5,5,5);
INSERT INTO receipts (customer_id, image_name, entry_date, text, amount, account, offset_account) VALUES (2,'','2012-08-14','TEST 6',6,6,6);
INSERT INTO receipts (customer_id, image_name, entry_date, text, amount, account, offset_account) VALUES (3,'','2012-08-14','TEST 7',7,7,7);
INSERT INTO receipts (customer_id, image_name, entry_date, text, amount, account, offset_account) VALUES (3,'','2012-08-14','TEST 8',8,8,8);

DROP VIEW IF EXISTS rnum_next; 
CREATE VIEW rnum_next AS SELECT customer_id, (MAX(receipt_number)+1) AS receipt_next FROM receipts GROUP BY customer_id ORDER BY customer_id;

DROP TABLE IF EXISTS customers;
CREATE TABLE customers (id SERIAL PRIMARY KEY, email VARCHAR(255));
CREATE UNIQUE INDEX email_idx ON customers(email);

DROP TABLE IF EXISTS cust_devices;
CREATE TABLE cust_devices(id serial PRIMARY KEY, customer_id INT, udid VARCHAR(100));
