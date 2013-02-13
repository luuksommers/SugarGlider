-- this script installs the complete database

-- drop database nfsurvey;
-- create database nfsurvey;
-- drop  user 'nfuser'@'localhost';
create user 'nfuser'@'localhost' identified by 'glidersugar';
grant select,insert,update,delete on nfsurvey.* to 'nfuser'@'localhost';

use nfsurvey;

create table paj_session
(
	id int unsigned auto_increment not null,
	sessionid varchar( 50 ) not null,
	startdatetime datetime not null,
	enddatetime datetime,
	primary key( id )
);

create table paj_survey
(
	id int unsigned auto_increment not null,
	title varchar( 30 ) not null,
	description varchar( 2048 ),
	asklogin tinyint( 1 ) default 0,
	askinfo tinyint( 1 ) default 0,
	questionsperpage smallint default 0,
	primary key( id )
);

create table paj_surveyuser
(
	id int unsigned auto_increment not null,
	surveyid int unsigned not null,
	sessionid int unsigned not null,
	firstname varchar( 50 ),
	lastname varchar( 50 ),
	birthdate datetime,
	externalid varchar( 50 ),
	username varchar( 50 ),
	password varchar( 30 ) not null,
	used tinyint( 1 ) default 0,
	foreign key (surveyid) references paj_survey(id),
	foreign key (sessionid) references paj_session(id),
	index (surveyid),
	primary key( id )
);


create table paj_questiontype
(
	id int unsigned not null,
	name varchar( 50 ) not null,
	primary key( id )
);

create table paj_questiongroup
(
	id int unsigned auto_increment not null,
	name varchar( 50 ) not null,
	lowestrate varchar( 50 ) not null,
	highestrate varchar( 50 ) not null,
	alternativerate varchar( 50 ),
	numberofoptions int unsigned not null,
	primary key( id )
);


create table paj_question 
(
	id int unsigned auto_increment not null,
	surveyid int unsigned not null,
	questiontypeid int unsigned not null default 1,
	questiongroupid int unsigned default 0,
	question varchar( 250 ) not null,
	commentflag tinyint( 1 ) default 0,
	primary key( id ),
	foreign key (surveyid) references paj_survey(id),
	foreign key (questiontypeid) references paj_questiontype(id),
	index (surveyid),
	index (questiontypeid),
	index (questiongroupid)
);

create table paj_answer
(
	id int unsigned auto_increment not null,
	questionid int unsigned not null,
	answer varchar( 250 ) not null,
	primary key( id ),
	foreign key (questionid) references paj_question(id),
	index (questionid)
);

create table paj_vote
(
	id int unsigned auto_increment not null,
	sessionid int unsigned not null,
	surveyid int unsigned not null,
	questionid int unsigned not null,
	answerid int unsigned default 0,
	rateid int unsigned default 0,
	answertext varchar( 500 ),
	comment varchar( 500 ),
	primary key( id ),
	foreign key (surveyid) references paj_survey(id),
	foreign key (sessionid) references paj_session(id),
	foreign key (questionid) references paj_question(id),
	index (surveyid),
	index (sessionid),
	index (questionid),
	index (answerid)
);

insert into paj_questiontype ( id, name ) values ( 1, "normal" );
insert into paj_questiontype ( id, name ) values ( 2, "rate" );
insert into paj_questiontype ( id, name ) values ( 3, "multiple" );
insert into paj_questiontype ( id, name ) values ( 4, "open" );
insert into paj_questiontype ( id, name ) values ( 5, "open multi" );

insert into paj_questiongroup ( name, lowestrate, highestrate, alternativerate, numberofoptions ) values ( "weinig - veel","heel weinig", "heel veel", "geen mening", 5 );
insert into paj_questiongroup ( name, lowestrate, highestrate, alternativerate, numberofoptions ) values ( "slecht - goed","heel slecht", "heel goed", "geen mening", 5 );
insert into paj_questiongroup ( name, lowestrate, highestrate, alternativerate, numberofoptions ) values ( "zelden - vaak","heel zelden", "heel vaak", "geen mening", 5 );
insert into paj_questiongroup ( name, lowestrate, highestrate, alternativerate, numberofoptions ) values ( "ja - nee","ja", "nee", "geen mening", 2 );
insert into paj_questiongroup ( name, lowestrate, highestrate, alternativerate, numberofoptions ) values ( "1x pd - 1x pw","1 keer per dag", "eens per week", "geen mening", 2 );
insert into paj_questiongroup ( name, lowestrate, highestrate, alternativerate, numberofoptions ) values ( "nooit - altijd","nooit", "altijd", "geen mening", 5 );
