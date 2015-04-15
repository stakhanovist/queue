/*
Sample grant for SQLite

CREATE ROLE queue LOGIN
  PASSWORD '[CHANGE ME]'
  NOSUPERUSER NOINHERIT NOCREATEDB NOCREATEROLE;

*/

--
-- Table structure for table queue
--

CREATE TABLE message
(
  message_id integer PRIMARY KEY AUTOINCREMENT,
  queue_id integer NOT NULL,
  handle char(32) default NULL,
  class varchar(255) NOT NULL,
  content varchar(8192) NOT NULL,
  metadata text default NULL,
  md5 char(32) NOT NULL,
  timeout bigint(20) default NULL,
  schedule bigint(20) default NULL,
  interval bigint(20) default NULL,
  created bigint(20) NOT NULL
);




-- --------------------------------------------------------
--
-- Table structure for table message
--

CREATE TABLE queue
(
  queue_id integer PRIMARY KEY AUTOINCREMENT,
  queue_name varchar(100) NOT NULL,
  FOREIGN KEY (queue_id) REFERENCES queue(queue_id)
);

