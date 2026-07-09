<?php
require_once 'Database.php';

/* ==========================================================
   LINK MODEL
   Handles all database interactions related to URL shortening,
   click tracking, traffic aggregation, and analytics.
========================================================== */
class Link {
    /**
     * @var PDO Database connection instance.
     */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /* ==========================================================
       CORE LINK MANAGEMENT
       Methods for generating, retrieving, modifying, and 
       deleting shortened URL records.
    ========================================================== */
    
    /**
     * Verifies if a specific short code is already in use.
     */
    public function shortCodeExists($code) {
        $sql = "SELECT id FROM links WHERE short_code = :code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch() !== false;
    }

    /**
     * Inserts a new shortened link record for a specific user.
     */
    public function createShortLink($userId, $originalUrl, $customCode, $title = null, $password = null, $expiresAt = null) {
        if ($this->shortCodeExists($customCode)) {
            return false;
        }

        $hashedPassword = $password ? password_hash($password, PASSWORD_BCRYPT) : null;

        $sql = "INSERT INTO links (user_id, original_url, short_code, title, password, expires_at) 
                VALUES (:user_id, :url, :code, :title, :password, :expires_at)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':url' => $originalUrl,
            ':code' => $customCode,
            ':title' => $title,
            ':password' => $hashedPassword,
            ':expires_at' => $expiresAt
        ]);
    }

    /**
     * Modifies the metadata (title, destination) of an existing link.
     */
    public function updateLink($linkId, $userId, $title, $originalUrl) {
        $sql = "UPDATE links SET title = :title, original_url = :url WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':url' => $originalUrl,
            ':id' => $linkId,
            ':user_id' => $userId
        ]);
    }

    /**
     * Retrieves the complete library of links associated with a user.
     */
    public function getUserLinks($userId) {
        $sql = "SELECT id, original_url, short_code, title, clicks_count, password, expires_at, created_at 
                FROM links WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Removes a link from the system, restricted by user ownership.
     */
    public function deleteLink($linkId, $userId) {
        $sql = "DELETE FROM links WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $linkId,
            ':user_id' => $userId
        ]);
    }

    /**
     * Resolves a short code to its routing configuration (destination URL).
     */
    public function getLinkByCode($shortCode) {
        $sql = "SELECT id, original_url, password, expires_at FROM links WHERE short_code = :code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $shortCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================================================
       TRAFFIC TRACKING
       Records incoming interactions and increments associated
       statistical counters utilizing database transactions.
    ========================================================== */
    
    /**
     * Logs a traffic event including IP, device footprint, and referrer.
     */
    public function logClick($linkId, $ipAddress, $userAgent, $referer) {
        try {
            $this->db->beginTransaction();

            $sqlCounter = "UPDATE links SET clicks_count = clicks_count + 1 WHERE id = :link_id";
            $stmtCounter = $this->db->prepare($sqlCounter);
            $stmtCounter->execute([':link_id' => $linkId]);

            $sqlClick = "INSERT INTO clicks (link_id, ip_address, user_agent, referer) 
                         VALUES (:link_id, :ip, :ua, :referer)";
            $stmtClick = $this->db->prepare($sqlClick);
            $stmtClick->execute([
                ':link_id' => $linkId,
                ':ip' => $ipAddress,
                ':ua' => $userAgent,
                ':referer' => $referer
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("SaaS Tracking Error: " . $e->getMessage());
            return false;
        }
    }

    /* ==========================================================
       ANALYTICS ENGINE
       Aggregates traffic data into structured formats required
       by the charting systems (ApexCharts, Three-Globe).
    ========================================================== */

    /**
     * Maps frontend period identifiers to PostgreSQL interval syntax.
     */
    private function getIntervalString($range) {
        switch ($range) {
            case '1d': return "INTERVAL '1 day'";
            case '30d': return "INTERVAL '30 days'";
            case '1y': return "INTERVAL '1 year'";
            default: return "INTERVAL '7 days'"; 
        }
    }

    /**
     * Generates a daily aggregation of total and unique clicks.
     */
    public function getClicksTimeline($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        
        $sql = "SELECT CAST(c.clicked_at AS DATE) as click_date, 
                       COUNT(c.id) as click_count,
                       COUNT(DISTINCT c.ip_address) as unique_count
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id";
        
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }
        
        $sql .= " AND c.clicked_at >= CURRENT_DATE - $interval
                GROUP BY 1
                ORDER BY click_date ASC";
                
        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculates absolute traffic volume over a defined period.
     */
    public function getTotalClicksByRange($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        $sql = "SELECT COUNT(c.id) 
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id
                AND c.clicked_at >= CURRENT_DATE - $interval";
        
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }

        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn() ?: 0;
    }

    /**
     * Calculates the count of unique IP addresses over a defined period.
     */
    public function getUniqueVisitorsCount($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        $sql = "SELECT COUNT(DISTINCT c.ip_address) 
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id
                AND c.clicked_at >= CURRENT_DATE - $interval";
        
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }

        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }

        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Classifies User-Agent strings into major operating systems.
     */
    public function getOsDistribution($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        
        $sql = "SELECT 
                    CASE 
                        WHEN c.user_agent ILIKE '%iPhone%' OR c.user_agent ILIKE '%iPad%' THEN 'iOS'
                        WHEN c.user_agent ILIKE '%Android%' THEN 'Android'
                        WHEN c.user_agent ILIKE '%Windows%' THEN 'Windows'
                        WHEN c.user_agent ILIKE '%Macintosh%' OR c.user_agent ILIKE '%Mac OS%' THEN 'macOS'
                        ELSE 'Другие'
                    END as os_name,
                    COUNT(c.id) as click_count
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id
                AND c.clicked_at >= CURRENT_DATE - $interval";
                
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }
        
        $sql .= " GROUP BY 1 ORDER BY click_count DESC";
        
        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Categorizes HTTP Referer strings to determine acquisition vectors.
     */
    public function getTrafficSources($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        
        $sql = "SELECT 
                    CASE 
                        WHEN c.referer IS NULL OR c.referer = '' THEN 'Прямой переход'
                        WHEN c.referer ILIKE '%t.me%' OR c.referer ILIKE '%telegram%' THEN 'Telegram'
                        WHEN c.referer ILIKE '%instagram.com%' THEN 'Instagram'
                        WHEN c.referer ILIKE '%vk.com%' THEN 'ВКонтакте'
                        WHEN c.referer ILIKE '%youtube.com%' THEN 'YouTube'
                        ELSE 'Другие сайты'
                    END as source_name,
                    COUNT(c.id) as click_count
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id
                AND c.clicked_at >= CURRENT_DATE - $interval";
                
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }
        
        $sql .= " GROUP BY 1 ORDER BY click_count DESC LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves the total volume of traffic processed in the last 24 hours.
     */
    public function getTotalClicksLast24h($userId) {
        $sql = "SELECT COUNT(c.id) 
                FROM clicks c 
                JOIN links l ON c.link_id = l.id 
                WHERE l.user_id = :user_id 
                AND c.clicked_at >= NOW() - INTERVAL '24 hours'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn();
    }

    /**
     * Identifies the highest performing link in a user's library.
     */
    public function getTopLink($userId) {
        $sql = "SELECT title, short_code, clicks_count 
                FROM links 
                WHERE user_id = :user_id AND clicks_count > 0 
                ORDER BY clicks_count DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Converts an IPv4 address into approximate latitude/longitude coordinates
     * using a deterministic hash for visualization purposes.
     */
    private function ipToCoords($ip) {
        if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
            return [46.4825, 30.7233]; 
        }
        $hash = crc32($ip);
        $lat = (($hash % 140) - 70) + (($hash % 1000) / 1000);
        $lng = (($hash % 320) - 160) + (($hash % 1000) / 1000);
        return [round($lat, 4), round($lng, 4)];
    }

    /**
     * Compiles formatted coordinate data for the Three-Globe visualization.
     */
    public function getGlobePoints($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        $sql = "SELECT c.ip_address, COUNT(c.id) as weight
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id
                AND c.clicked_at >= CURRENT_DATE - $interval";
        
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }
        $sql .= " GROUP BY c.ip_address";

        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $points = [];
        foreach ($records as $rec) {
            $coords = $this->ipToCoords($rec['ip_address']);
            $points[] = [
                'lat' => $coords[0],
                'lng' => $coords[1],
                'size' => min(0.4, 0.05 * $rec['weight']),
                'ip' => $rec['ip_address']
            ];
        }
        return $points;
    }

    /**
     * Synthesizes OHLC (Open, High, Low, Close) candlestick data 
     * based on traffic volume volatility.
     */
    public function getOhlcData($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        
        $sql = "SELECT CAST(c.clicked_at AS DATE) as click_date, COUNT(c.id) as volume 
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id";
                
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }
        
        $sql .= " AND c.clicked_at >= CURRENT_DATE - $interval
                GROUP BY 1
                ORDER BY click_date ASC";
                
        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }
        
        $stmt->execute($params);
        $rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ohlc = [];
        $prevClose = 10; 
        
        foreach ($rawData as $row) {
            $vol = (int)$row['volume'];
            if ($vol === 0) continue;
            
            $open = $prevClose;
            $high = $open + ($vol * 1.5) + rand(1, 5);
            $low = max(1, $open - ($vol * 0.5) - rand(1, 3));
            $close = ($vol % 2 === 0) ? $open + ($vol * 0.8) : $open - ($vol * 0.4);
            
            if ($close > $high) $high = $close + 1;
            if ($close < $low) $low = $close - 1;
            if ($low < 1) $low = 1;

            $ohlc[] = [
                'x' => strtotime($row['click_date']) * 1000,
                'y' => [round($open, 2), round($high, 2), round($low, 2), round($close, 2)]
            ];
            $prevClose = $close;
        }
        return $ohlc;
    }

    /**
     * Determines the percentage of traffic originating from mobile devices.
     */
    public function getMobileTrafficShare($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        $sql = "SELECT 
                    COUNT(CASE WHEN c.user_agent ILIKE '%Mobile%' OR c.user_agent ILIKE '%Android%' OR c.user_agent ILIKE '%iPhone%' THEN 1 END) * 100.0 / NULLIF(COUNT(c.id), 0) as mobile_pct
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id
                AND c.clicked_at >= CURRENT_DATE - $interval";
                
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }
        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }
        $stmt->execute($params);
        $result = $stmt->fetchColumn();
        return $result ? round((float)$result, 1) : 0;
    }

    /**
     * Identifies the most frequent incoming IP address for geographic resolution.
     */
    public function getTopIp($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        $sql = "SELECT c.ip_address
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id
                AND c.clicked_at >= CURRENT_DATE - $interval";
                
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }
        $sql .= " GROUP BY c.ip_address ORDER BY COUNT(c.id) DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Aggregates traffic volume distributed across a 24-hour UTC cycle.
     */
    public function getHourlyActivity($userId, $linkId = null, $range = '7d') {
        $interval = $this->getIntervalString($range);
        
        $sql = "SELECT EXTRACT(HOUR FROM c.clicked_at AT TIME ZONE 'UTC') as hour_utc, COUNT(c.id) as click_count 
                FROM clicks c
                JOIN links l ON c.link_id = l.id
                WHERE l.user_id = :user_id
                AND c.clicked_at >= CURRENT_DATE - $interval";
                
        if ($linkId) {
            $sql .= " AND l.id = :link_id";
        }
        $sql .= " GROUP BY hour_utc";
        
        $stmt = $this->db->prepare($sql);
        $params = [':user_id' => $userId];
        if ($linkId) {
            $params[':link_id'] = $linkId;
        }
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hourlyData = array_fill(0, 24, 0);
        foreach ($results as $row) {
            $hour = (int)$row['hour_utc'];
            $hourlyData[$hour] = (int)$row['click_count'];
        }
        
        return $hourlyData;
    }
}