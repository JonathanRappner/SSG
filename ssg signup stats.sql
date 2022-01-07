--Positiva anmälningar
SELECT
	m.name,
	COUNT(s.event_id) AS signups_count
FROM ssg_members AS m
JOIN ssg_signups AS s
	ON m.id = s.member_id
WHERE
	s.signed_datetime BETWEEN '2021-01-01' AND '2021-12-31'
	AND m.is_active = 1
	AND s.attendance <= 3
GROUP BY m.id
ORDER BY signups_count DESC

--Antal timmar (genomsnitt) i förväg som anmälan gjorts
SELECT
	m.name,
    ROUND(AVG(DATEDIFF(e.start_datetime, s.signed_datetime)), 1) AS signed_time
FROM ssg_members AS m
JOIN ssg_signups AS s
	ON m.id = s.member_id
JOIN ssg_events AS e
	ON s.event_id = e.id
WHERE
	s.signed_datetime BETWEEN '2021-01-01' AND '2021-12-31'
	AND m.is_active = 1
    AND s.attendance <= 3
GROUP BY m.id
ORDER BY signed_time DESC;