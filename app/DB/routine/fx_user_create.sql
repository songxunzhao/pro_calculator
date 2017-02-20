DROP PROCEDURE IF EXISTS fx_user_create;
CREATE PROCEDURE fx_user_create(
    IN p_uuid_hash                              VARCHAR(255)
,   IN p_email                                  VARCHAR(255)
)
  BEGIN
    INSERT INTO user
    (
      `uuid_hash`,
      `email`,
      `created_at`,
      `updated_at`
    )
    VALUES
    (
        p_uuid_hash,
        p_email,
        NOW(),
        NOW()
    );
  END
