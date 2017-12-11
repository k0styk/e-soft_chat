CREATE DATABASE "chatDB"
  WITH OWNER = postgres
       ENCODING = 'UTF8'
       TABLESPACE = pg_default
       LC_COLLATE = 'Russian_Russia.1251'
       LC_CTYPE = 'Russian_Russia.1251'
       CONNECTION LIMIT = -1;

DROP TABLE IF EXISTS public.messages;
CREATE TABLE public.messages
(
  "ID" serial primary key,
  room_id integer NOT NULL,
  uid text NOT NULL,
  message text
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.messages
  OWNER TO postgres;

DROP TABLE IF EXISTS public.room_blocked;
CREATE TABLE public.room_blocked
(
  "ID" serial primary key,
  room_id integer NOT NULL,
  uid text NOT NULL,
  expiration timestamp without time zone
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.room_blocked
  OWNER TO postgres;

DROP TABLE IF EXISTS public.rooms;
CREATE TABLE public.rooms
(
  "ID" serial primary key,
  name text
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.rooms
  OWNER TO postgres;

ALTER TABLE public.messages ADD CONSTRAINT "messages_FK_room" FOREIGN KEY (room_id) REFERENCES public.rooms ("ID") MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;
ALTER TABLE public.room_blocked  ADD CONSTRAINT "room_blocked_FK_room" FOREIGN KEY (room_id) REFERENCES public.rooms ("ID") MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;