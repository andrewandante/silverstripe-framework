<?php

class TrustedProxiesTest extends \SapphireTest {

	public function testSingleIPv4MatchesSingleIPv4() {
		$ip = '11.11.11.11';
		$range = '11.11.11.11';
		assertTrue(ip_in_ipv4_range($ip, $range));
	}

	public function testSingleIPv4MatchesIPv4Range() {
		$ip = '11.11.11.11';
		$range = '11.11.11.0/24';
		assertTrue(ip_in_ipv4_range($ip, $range));
	}

	public function testSingleIPv6MatchesSingleIPv6() {
		$ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
		$range = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
		assertTrue(ip_in_ipv6_range($ip, $range));
	}

	public function testSingleIPv6MatchesIPv6Range() {
		$ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
		$range = '2001:0db8:85a3::/48';
		assertTrue(ip_in_ipv6_range($ip, $range));
	}

	public function testCompressedIPv6MatchesIPv6Range() {
		$ip = '2001:0db8:85a3::8a2e:370:7334';
		$range = '2001:0db8:85a3::/48';
		assertTrue(ip_in_ipv6_range($ip, $range));
	}

}
