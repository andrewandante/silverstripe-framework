<?php

class TrustedProxiesTest extends SapphireTest {

	public function testIPv4Range() {
		$ip = '11.11.11.11';

		$correctIP = '11.11.11.11';
		$incorrectIP = '22.22.22.22';
		$correctRange = '11.11.11.0/24';
		$incorrectRange = '22.22.22.0/24';
		$invalidSubnet = '11.11.11.11/33';
		$invalidCIDR = '11.11.11/0';
		$invalidIP = '11.1111.11.1111';
		$nanIP = 'not_a_number';

		$this->assertTrue(ip_in_ipv4_range($ip, $correctIP));
		$this->assertTrue(ip_in_ipv4_range($ip, $correctRange));

		$this->assertFalse(ip_in_ipv4_range($ip, $incorrectIP));
		$this->assertFalse(ip_in_ipv4_range($ip, $incorrectRange));
		$this->assertFalse(ip_in_ipv4_range($ip, $invalidSubnet));
		$this->assertFalse(ip_in_ipv4_range($ip, $invalidCIDR));
		$this->assertFalse(ip_in_ipv4_range($ip, $invalidIP));
		$this->assertFalse(ip_in_ipv4_range($ip, $nanIP));


	}

	public function testIPv6Range() {
		$ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

		$compressedIP = '2001:db8:85a3::8a2e:370:7334';
		$correctIP = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
		$compressedCorrectIP = '2001:db8:85a3::8a2e:370:7334';
		$incorrectIP = '2001:0db8:85a3:1111:1111:8a2e:0370:5555';
		$correctRange = '2001:0db8:85a3::/48';
		$incorrectRange = '2001:0db8:8585::/48';

		$this->assertTrue(ip_in_ipv6_range($ip, $correctIP));
		$this->assertTrue(ip_in_ipv6_range($compressedIP, $correctIP));
		$this->assertTrue(ip_in_ipv6_range($ip, $compressedCorrectIP));
		$this->assertTrue(ip_in_ipv6_range($compressedIP, $compressedCorrectIP));
		$this->assertFalse(ip_in_ipv6_range($ip, $incorrectIP));
		$this->assertTrue(ip_in_ipv6_range($ip, $correctRange));
		$this->assertFalse(ip_in_ipv6_range($ip, $incorrectRange));
	}

	public function testRanges() {
		$ranges = '127.0.0.1, 11.11.0.0/16, ::1, 2001:db8:85a3::/48';

		$correctIPv6= '::1';
		$correctIPv6Range = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
		$correctIPv4 = '127.0.0.1';
		$correctIPv4Range = '11.11.11.11';

		$incorrectIPv6 = '::2';
		$incorrectIPv4 = '192.168.1.168';

		$this->assertTrue(ip_in_any_range($correctIPv6, $ranges));
		$this->assertTrue(ip_in_any_range($correctIPv6Range, $ranges));
		$this->assertTrue(ip_in_any_range($correctIPv4, $ranges));
		$this->assertTrue(ip_in_any_range($correctIPv4Range, $ranges));

		$this->assertFalse(ip_in_any_range($incorrectIPv6, $ranges));
		$this->assertFalse(ip_in_any_range($incorrectIPv4, $ranges));

	}

}
