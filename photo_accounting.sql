-- --To create the database :
-- sudo -u postgres createdb photo_accounting

-- -- To execute this script within a PostgreSQL session (adjust path accordingly) :
-- \i /home/pmg/Documents/photo_accounting/www/PhotoAccounting/sql/photo_accounting.sql

-- DROP TABLE IF EXISTS entries;
DROP TABLE IF EXISTS receipts;
CREATE TABLE receipts (	id SERIAL PRIMARY KEY, 
						customer_id INT, 
						receipt_number SERIAL,
						image_type character varying(255), 
						entry_date DATE DEFAULT current_date, 
						text VARCHAR(9999), 
						amount NUMERIC(20, 2) DEFAULT 0.00, 
						account INT, 
						offset_account INT, 
						s3url varchar(300), 
						received_date timestamp);

INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (1,'','2012-08-14','TEST 1',1,1,1);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (2,'','2012-08-14','TEST 2',2,2,2);
-- INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (3,'','2012-08-14','TEST 3',3,3,3);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (4,'','2012-08-14','TEST 4',4,4,4);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (5,'','2012-08-14','TEST 5',5,5,5);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (6,'','2012-08-14','TEST 6',6,6,6);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (7,'','2012-08-14','TEST 7',7,7,7);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (8,'','2012-08-14','TEST 8',8,8,8);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (9,'','2012-08-14','TEST 9',9,9,9);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (10,'','2012-08-14','TEST 10',10,10,10);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (11,'','2012-08-14','TEST 11',11,11,11);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (12,'','2012-08-14','TEST 12',12,12,12);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (13,'','2012-08-14','TEST 13',13,13,13);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (14,'','2012-08-14','TEST 14',14,14,14);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (15,'','2012-08-14','TEST 15',15,15,15);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (16,'','2012-08-14','TEST 16',16,16,16);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (17,'','2012-08-14','TEST 17',17,17,17);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (18,'','2012-08-14','TEST 18',18,18,18);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (19,'','2012-08-14','TEST 19',19,19,19);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (20,'','2012-08-14','TEST 20',20,20,20);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (21,'','2012-08-14','TEST 21',21,21,21);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (22,'','2012-08-14','TEST 22',22,22,22);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (23,'','2012-08-14','TEST 23',23,23,23);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (24,'','2012-08-14','TEST 24',24,24,24);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (25,'','2012-08-14','TEST 25',25,25,25);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (26,'','2012-08-14','TEST 26',26,26,26);
INSERT INTO receipts (customer_id, image_type, entry_date, text, amount, account, offset_account) VALUES (27,'','2012-08-14','TEST 27',27,27,27); 

DROP TABLE IF EXISTS customers;
CREATE TABLE customers (customer_id SERIAL PRIMARY KEY, email varchar(255));

drop table if exists cust_devices;
create table cust_devices(id serial primary key, customer_id int, device_id varchar(100));