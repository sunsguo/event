create database events;

use events;

create table if not exists events(
event_id int not null auto_increment,
event_title varchar(80) default null,
event_desc text,
event_start timestamp not null default '0000-00-00 00:00:00',
event_end timestamp not null default '0000-00-00 00:00:00',
primary key (event_id),
index (event_start)
);

insert into events(event_title, event_desc, event_start, event_end) 
values('New Year&#039;s Day', 'Happy New Year!', '2010-01-01 00:00:00', '2010-01-01 23:59:59'),
 ('Last Day of January', 'Last Day of the month! Yay!', '2010-01-31 00:00:00', '2010-01-31 23:59:59');
 
 create table users(
 user_id int not null auto_increment,
 user_name varchar(20) default null,
 user_pass varchar(20) default null,
 user_email varchar(30) default null,
 primary key (user_id),
 unique (user_name)
 );

 insert into users(user_name, user_pass, user_email) values('gy', '123456', '2791273299@qq.com');