
ALTER TABLE user ADD Tags text AFTER Verified;

ALTER TABLE user ADD FULLTEXT (`Tags`);

ALTER TABLE user ADD Active tinyint(1) default 1 AFTER Verified;

