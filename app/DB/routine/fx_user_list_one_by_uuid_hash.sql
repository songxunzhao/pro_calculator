DROP PROCEDURE IF EXISTS fx_user_list_one_by_uuid_hash;
CREATE PROCEDURE fx_user_list_one_by_uuid_hash(
    IN p_uuid_hash                              VARCHAR(255)
)
BEGIN
  SELECT *
  FROM user
  WHERE `uuid_hash` = p_uuid_hash;
END
