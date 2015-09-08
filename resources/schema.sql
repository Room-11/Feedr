--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

CREATE SCHEMA IF NOT EXISTS public;
CREATE SCHEMA IF NOT EXISTS pg_catalog;

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;
COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';

SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;



--
-- Table: admins
--

CREATE TABLE public.admins
(
  id bigint NOT NULL,
  feed_id bigint NOT NULL,
  user_id bigint NOT NULL
);

CREATE SEQUENCE public.admins_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1;

ALTER TABLE public.admins
  OWNER TO webserverprocess;

ALTER TABLE public.admins_id_seq
  OWNER TO webserverprocess;

ALTER SEQUENCE public.admins_id_seq
  OWNED BY admins.id;

ALTER TABLE ONLY public.admins
  ALTER COLUMN id SET DEFAULT nextval('admins_id_seq'::regclass);

ALTER TABLE ONLY public.admins
  ADD CONSTRAINT pk_admins_id PRIMARY KEY (id);



--
-- Table: auth_log
--

CREATE TABLE public.auth_log
(
  id bigint NOT NULL,
  user_id bigint NOT NULL,
  "timestamp" timestamp without time zone DEFAULT now() NOT NULL,
  ip inet NOT NULL
);

CREATE SEQUENCE public.auth_log_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1;

ALTER TABLE public.auth_log
  OWNER TO webserverprocess;

ALTER TABLE public.auth_log_id_seq
  OWNER TO webserverprocess;

ALTER SEQUENCE public.auth_log_id_seq
  OWNED BY auth_log.id;

ALTER TABLE ONLY public.auth_log
  ALTER COLUMN id SET DEFAULT nextval('auth_log_id_seq'::regclass);

ALTER TABLE ONLY public.auth_log
  ADD CONSTRAINT pk_auth_log_id PRIMARY KEY (id);



--
-- Table: feeds
--

CREATE TABLE public.feeds
(
  id bigint NOT NULL,
  name character varying(255) NOT NULL
);

CREATE SEQUENCE public.feeds_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1;

ALTER TABLE public.feeds
  OWNER TO webserverprocess;

ALTER TABLE public.feeds_id_seq
  OWNER TO webserverprocess;

ALTER SEQUENCE public.feeds_id_seq
  OWNED BY feeds.id;

ALTER TABLE ONLY public.feeds
  ALTER COLUMN id SET DEFAULT nextval('feeds_id_seq'::regclass);

ALTER TABLE ONLY public.feeds
  ADD CONSTRAINT pk_feeds_id PRIMARY KEY (id);



--
-- Table: feeds_repositories
--

CREATE TABLE public.feeds_repositories
(
  id bigint NOT NULL,
  feed_id bigint NOT NULL,
  repository character varying(255) NOT NULL,
  repository_id bigint NOT NULL
);

CREATE SEQUENCE public.feeds_repositories_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1;

ALTER TABLE public.feeds_repositories
  OWNER TO webserverprocess;

ALTER TABLE public.feeds_repositories_id_seq
  OWNER TO webserverprocess;

ALTER SEQUENCE public.feeds_repositories_id_seq
  OWNED BY feeds_repositories.id;

ALTER TABLE ONLY public.feeds_repositories
  ALTER COLUMN id SET DEFAULT nextval('feeds_repositories_id_seq'::regclass);

ALTER TABLE ONLY public.feeds_repositories
  ADD CONSTRAINT pk_feeds_repositories_id PRIMARY KEY (id);



--
-- Table: log
--

CREATE TABLE public.log
(
  id bigint NOT NULL,
  user_id bigint,
  feed_id bigint,
  post_id bigint,
  "timestamp" timestamp without time zone NOT NULL,
  type character varying(128) NOT NULL
);

CREATE SEQUENCE public.log_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1;

ALTER TABLE public.log
  OWNER TO webserverprocess;

ALTER TABLE public.log_id_seq
  OWNER TO webserverprocess;

ALTER SEQUENCE public.log_id_seq
  OWNED BY log.id;

ALTER TABLE ONLY public.log
  ALTER COLUMN id SET DEFAULT nextval('log_id_seq'::regclass);

ALTER TABLE ONLY public.log
  ADD CONSTRAINT pk_log_id PRIMARY KEY (id);



--
-- Table: posts
--

CREATE TABLE public.posts
(
  id bigint NOT NULL,
  release_id bigint NOT NULL,
  feed_repository_id bigint NOT NULL,
  avatar_url character varying(255) NOT NULL,
  version character varying(128) NOT NULL,
  "timestamp" timestamp without time zone NOT NULL,
  content text,
  url character varying(255) NOT NULL,
  username character varying(255) NOT NULL
);

CREATE SEQUENCE public.posts_id_seq
  START WITH 1
  INCREMENT BY 1
  NO MINVALUE
  NO MAXVALUE
  CACHE 1;

ALTER TABLE public.posts
  OWNER TO webserverprocess;

ALTER TABLE public.posts_id_seq
  OWNER TO webserverprocess;

ALTER SEQUENCE public.posts_id_seq
  OWNED BY posts.id;

ALTER TABLE ONLY public.posts
  ALTER COLUMN id SET DEFAULT nextval('posts_id_seq'::regclass);

ALTER TABLE ONLY public.posts
  ADD CONSTRAINT pk_posts_id PRIMARY KEY (id);



--
-- Table: users
--

CREATE TABLE public.users
(
  id bigint NOT NULL,
  username character varying(128) NOT NULL
);

ALTER TABLE public.users
  OWNER TO webserverprocess;

ALTER TABLE ONLY public.users
  ADD CONSTRAINT pk_users_id PRIMARY KEY (id);
