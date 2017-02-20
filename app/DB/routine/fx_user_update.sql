#DROP PROCEDURE IF EXISTS fx_user_update;
CREATE PROCEDURE fx_user_update(
    IN p_uuid_hash                              VARCHAR(255)
,   IN p_email                                  VARCHAR(255)
)
BEGIN
  UPDATE `user`
  SET
    `email`       = p_email
  , `updated_at`  = NOW()
  WHERE
    `uuid_hash`   = p_uuid_hash;

  SELECT *
  FROM `user`
  WHERE `uuid_hash` = p_uuid_hash;
END
