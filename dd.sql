



CREATE TABLE blog(
id                      INT(11)                 AUTO_INCREMENT PRIMARY KEY,
status                  INT(11)                 NOT NULL,
user_id                 INT(11)                 NOT NULL,        
stamp                   TIMESTAMP               NULL DEFAULT NULL,
heading                 VARCHAR(64)             NOT NULL,
body                    TEXT,
slug                    VARCHAR(64)             NULL,
FOREIGN KEY (user_id) REFERENCES e_user(id)) ENGINE=InnoDB;



CREATE TABLE tag(
id                      INT(11)                  AUTO_INCREMENT PRIMARY KEY,
tag                     VARCHAR(128)             NOT NULL) ENGINE=InnoDB;



CREATE TABLE blog_tag(
blog_id                 INT(11)                  NOT NULL,
tag_id                  INT(11)                  NOT NULL,
PRIMARY KEY(blog_id, tag_id),
FOREIGN KEY(blog_id) REFERENCES blog(id),
FOREIGN KEY(tag_id) REFERENCES tag(id)) ENGINE=InnoDB;



CREATE TABLE blog_image(
id                      INT(11)                  AUTO_INCREMENT PRIMARY KEY,
blog_id                 INT(11)                  NOT NULL,
filename                VARCHAR(64)              NOT NULL,
image_width             VARCHAR(11)              NULL,
image_height            VARCHAR(11)              NULL,
FOREIGN KEY(blog_id) REFERENCES blog(id)) ENGINE=InnoDB;




CREATE TABLE blog_comment(
id                      INT(11)                 AUTO_INCREMENT PRIMARY KEY,
blog_id                 INT(11)                 NOT NULL,
user_id                 INT(11)                 NOT NULL,
status                  INT(11)                 NOT NULL,       
stamp                   TIMESTAMP               NOT NULL,
heading                 VARCHAR(64)             NULL,
body                    TEXT,
FOREIGN KEY(blog_id) REFERENCES blog(id),
FOREIGN KEY(user_id) REFERENCES e_user(id)) ENGINE=InnoDB;



CREATE TABLE blog_category(
id                      INT(11)                 AUTO_INCREMENT PRIMARY KEY,
blog_category_id        INT(11) NULL,
label                   VARCHAR(64) NOT NULL,
FOREIGN KEY(blog_category_id) REFERENCES blog_category(id)) ENGINE=InnoDB;




CREATE TABLE blog_item_category(
blog_id                 INT(11)                 NOT NULL,
blog_category_id        INT(11)                 NOT NULL,
FOREIGN KEY(blog_id) REFERENCES blog(id),
FOREIGN KEY(blog_category_id) REFERENCES blog_category(id),
PRIMARY KEY(blog_id, blog_category_id));



CREATE TABLE blog_attachment(
id                      INT(11)                 AUTO_INCREMENT PRIMARY KEY,
blog_id                 INT(11)                 NOT NULL,
filename                VARCHAR(128)            NULL,
url                     VARCHAR(128)            NULL,
FOREIGN KEY(blog_id) REFERENCES blog(id)) ENGINE=InnoDB;

