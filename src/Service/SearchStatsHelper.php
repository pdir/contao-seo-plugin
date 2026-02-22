<?php

namespace Pdir\ContaoSeoPlugin\Service;

use Doctrine\DBAL\Connection;

class SearchStatsHelper
{
    public function __construct(
        Connection $connection,
        ?string $secret
    )
    {
        $this->connection = $connection;
        $this->secret = $secret;
    }

    public function createOrUpdate($keywords): string
    {
        $year = (int) date('Y');
        $month = (int) date('n');

        // Check whether the keyword already exists for this month
        $obj = $this->connection->fetchAssociative(
            'SELECT id, count FROM tl_search_stats WHERE keywords=? AND year=? AND month=?',
            [$keywords, $year, $month]
        );

        // Update
        if (false !== $obj) {
            $this->connection->executeStatement(
                'UPDATE tl_search_stats SET count=count+1 WHERE id=?',
                [$obj['id']]
            );
        }

        // Insert
        if (false === $obj) {
            $this->connection->executeStatement(
                'INSERT INTO tl_search_stats (tstamp, keywords, count, year, month) VALUES (?, ?, ?, ?, ?)',
                [time(), $keywords, 1, $year, $month]
            );
        }
    }

    /**
     * Generate a hashed string that is URL save
     * @param  string $string The string to hash
     * @return string         Hashed string
     */
    function urlSafeHashEncode(string $str): string
    {
        $str = openssl_encrypt($str,"AES-128-ECB", $this->secret);

        return strtr(base64_encode($str), '+/=', '-_,');
    }

    /**
     * Decode a URL safe hash string
     * @param  string $string The string to decipher
     * @return string         The deciphered string
     */
    public function urlSafeHashDecode(string $str): string
    {
        $str = base64_decode(strtr($str, '-_,', '+/='));

        return openssl_decrypt($str,"AES-128-ECB",$this->secret);
    }
}
