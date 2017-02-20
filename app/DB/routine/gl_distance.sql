DROP FUNCTION IF EXISTS gl_distance;
CREATE FUNCTION gl_distance(
    IN p_lat1                                    FLOAT
,   IN p_lng1                                    FLOAT
,   IN p_lat2                                    FLOAT
,   IN p_lng2                                    FLOAT
,   IN p_unit                                    VARCHAR(10)
)
RETURNS FLOAT
BEGIN
  DECLARE EARTH_RADIUS_CONSTANT FLOAT;

  IF p_unit = 'miles' THEN
    SET EARTH_RADIUS_CONSTANT = 3959;
  ELSE -- default to kilometers
    SET EARTH_RADIUS_CONSTANT = 6371;
  END IF;

  RETURN (EARTH_RADIUS_CONSTANT * acos( cos( radians(p_lat1) )
  * cos( radians(p_lat2) )
  * cos( radians(p_lng2) - radians(p_lng1)) + sin(radians(p_lat1))
  * sin( radians(p_lat2)))
  );
END
