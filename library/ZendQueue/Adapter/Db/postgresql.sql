/*
Sample grant for PostgreSQL

CREATE ROLE queue LOGIN
  PASSWORD '[CHANGE ME]'
  NOSUPERUSER NOINHERIT NOCREATEDB NOCREATEROLE;

*/

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS queue;

CREATE TABLE queue
(
  queue_id serial NOT NULL,
  queue_name character varying(100) NOT NULL,
  PRIMARY KEY (queue_id)
)
WITH (OIDS=FALSE);
/*ALTER TABLE queue OWNER TO queue;*/


-- --------------------------------------------------------
--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS message;

CREATE TABLE message
(
  message_id bigserial NOT NULL,
  queue_id integer,
  handle character(32),
  class varchar(255) NOT NULL,
  content character varying(8192) NOT NULL,
  metadata character(8192),
  md5 character(32) NOT NULL,
  timeout integer,
  schedule integer,
  interval integer,
  created integer,
  PRIMARY KEY (message_id),
  CONSTRAINT message_ibfk_1 FOREIGN KEY (queue_id)
      REFERENCES queue (queue_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (OIDS=FALSE);
/*ALTER TABLE message OWNER TO queue;*/