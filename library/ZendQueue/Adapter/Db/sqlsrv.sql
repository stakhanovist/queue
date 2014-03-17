SET DATEFORMAT ymd

--- DROP DATABASE [zfqueue]
--- GO

CREATE DATABASE [zfqueue]
GO

USE [zfqueue]
GO

---- Table structure for table `queue`

CREATE TABLE [queue] (
  [queue_id]   BIGINT IDENTITY (1, 1),
  [queue_name] VARCHAR(100) NOT NULL,
  CONSTRAINT [queue_primary_queue] PRIMARY KEY NONCLUSTERED ([queue_id])
);

---- Table structure for table `message`

CREATE TABLE [message] (
  [message_id] BIGINT IDENTITY (1, 1),
  [queue_id]   BIGINT NOT NULL,
  [handle]     CHAR(32) NULL,
  [class]      VARCHAR(255) NOT NULL,
  [content]    VARCHAR(MAX) NOT NULL,
  [metadata]   NTEXT NULL,
  [md5]        CHAR(32) NOT NULL,
  [timeout]    BIGINT NULL,
  [schedule]   BIGINT NULL,
  [interval]   BIGINT NULL,
  [created]    BIGINT NOT NULL,
  CONSTRAINT [message_primary_message] PRIMARY KEY NONCLUSTERED ([message_id]),
  CONSTRAINT [message_message_ibfk_1] FOREIGN KEY ("queue_id") REFERENCES "queue" ("queue_id")
    ON UPDATE CASCADE
    ON DELETE CASCADE
);
--- CREATE UNIQUE NONCLUSTERED INDEX [message_message_handle] ON [message] ([handle]);
CREATE NONCLUSTERED INDEX [message_message_queueid] ON [message] ([queue_id]);
ALTER TABLE [message] CHECK CONSTRAINT [message_message_ibfk_1];
