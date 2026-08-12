<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait RankingAlert {
	public function sendNationalWinnerAlert() {
		// self::sendAlertRank_1();

		// self::sendAlertRank_2_5();

		// self::sendAlertRank_6_10();

		// self::sendAlertRank_11_20();

		// self::sendAlertRank_21_50();

		// self::sendAlertRank_51_100();

        self::sendUKAlertRank_1();

		self::sendUKAlertRank_2_3();

		self::sendUKAlertRank_4_5();
    }

    private function sendAlertRank_1 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 14,
            'start'     => 0,
            'limit'     => 1,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $address_url = sprintf("https://www.yaf.bribooks.com/addressrequest?uid=%s&code=%s&eid=14",
             $user_info['id'],
             $user_info['verification_code']
            );

            $subject  = sprintf("Congratulations %s - You Are Now India's #1 Best-Selling Author at SBWF 2024!", $rank['author_name']);

            $content = "<p>Dear " . ucwords($rank['author_name'] ?? 'Author') . ",</p>
            <p>We are thrilled to announce that you have become India’s #1 Best-Selling Young Author at the Summer Book Writing Festival 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p>As a winner, you will receive the following awards:</p>
            <ol>
                <li>MacBook Air</li>
                <li>Digital Certificate</li>
                <li>Feature on Disney International HD</li>
                <li>Feature in Crossword Book Store</li>
                <li>Digital National Media Coverage</li>
                <li>Physical Certificate & Trophy</li>
                <li>NDTV Interview</li>
                <li>Interview on BB TV YouTube Channel</li>
            </ol>
            <p><strong>IMPORTANT NOTE:</strong> Awards #1-5 will be delivered within 30 days. Please provide your complete address for the delivery of the MacBook Air by clicking the link below:</p>
            <p><a href=". $address_url.">Provide Address</a></p>
            <p>Awards #6-8 will be presented to you at the <strong>National Awards & Exhibition Ceremony</strong>, tentatively scheduled for 30th March 2025. The final date, schedule, and venue will be confirmed in the third week of January 2025.</p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a> or call us at 1800 309 9917.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            SBWF 2024<br>
            India</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }
    private function sendAlertRank_2_5 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 14,
            'start'     => 1,
            'limit'     => 4,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $address_url = sprintf("https://www.yaf.bribooks.com/addressrequest?uid=%s&code=%s&eid=14",
                $user_info['id'],
                $user_info['verification_code']
            );

            $subject  = sprintf("Congratulations %s - You Are Now India's Top 5 Best-Selling Author at SBWF 2024!", $rank['author_name']);

            $content = "<p>Dear " . ucwords($rank['author_name'] ?? 'Author') . ",</p>
            <p>We are thrilled to announce that you have become India's Top 5 Best-Selling Young Author at the Summer Book Writing Festival 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p>As a winner, you will receive the following awards:</p>
            <ol>
                <li>Lenovo Tab</li>
                <li>Digital Certificate</li>
                <li>Feature on Disney International HD</li>
                <li>Feature in Crossword Book Store</li>
                <li>Digital National Media Coverage</li>
                <li>Physical Certificate & Trophy</li>
                <li>NDTV Interview</li>
                <li>Interview on BB TV YouTube Channel</li>
            </ol>
            <p><strong>IMPORTANT NOTE:</strong> Awards #1-5 will be delivered within 30 working days. Please provide your complete address for the delivery of the Lenovo Tab by clicking the link below:</p>
            <p><a href=". $address_url.">Provide Address</a></p>
            <p>Awards #6-8 will be presented to you at the <strong>National Awards & Exhibition Ceremony</strong>, tentatively scheduled for 30th March 2025. The final date, schedule, and venue will be confirmed in the third week of January 2025.</p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a> or call us at 1800 309 9917.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            SBWF 2024<br>
            India</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }
    private function sendAlertRank_6_10 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 14,
            'start'     => 5,
            'limit'     => 5,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $address_url = sprintf("https://www.yaf.bribooks.com/addressrequest?uid=%s&code=%s&eid=14",
                $user_info['id'],
                $user_info['verification_code']
            );

            $subject  = sprintf("Congratulations %s - You Are Now India's 10 Best-Selling Author at SBWF 2024!", $rank['author_name']);

            $content = "<p>Dear " . ucwords($rank['author_name'] ?? 'Author') . ",</p>
            <p>We are thrilled to announce that you have become India's Top 10 Best-Selling Young Author at the Summer Book Writing Festival 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p>As a winner, you will receive the following awards:</p>
            <ol>
                <li>Lenovo Tab</li>
                <li>Digital Certificate</li>
                <li>Feature on Disney International HD</li>
                <li>Feature in Crossword Book Store</li>
                <li>Digital National Media Coverage</li>
                <li>Physical Certificate & Trophy</li>
                <li>Interview on BB TV YouTube Channel</li>
            </ol>
            <p><strong>IMPORTANT NOTE:</strong> Awards #1-5 will be delivered within 30 working days. Please provide your complete address for the delivery of the Lenovo Tab by clicking the link below:</p>
            <p><a href=". $address_url.">Provide Address</a></p>
            <p>Awards #6 & 7 will be presented to you at the <strong>National Awards & Exhibition Ceremony</strong>, tentatively scheduled for 30th March 2025. The final date, schedule, and venue will be confirmed in the third week of January 2025.</p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a> or call us at 1800 309 9917.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            SBWF 2024<br>
            India</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }
    private function sendAlertRank_11_20 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 14,
            'start'     => 10,
            'limit'     => 10,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $subject  = sprintf("Congratulations %s - You Are Now India's Top 20 Best-Selling Author at SBWF 2024!", $rank['author_name']);

            $content = "<p>Dear " . ucwords($rank['author_name'] ?? 'Author') . ",</p>
            <p>We are thrilled to announce that you have become India's Top 20 Best-Selling Young Author at the Summer Book Writing Festival 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p>As a winner, you will receive the following awards:</p>
            <ol>
                <li>Digital Certificate</li>
                <li>Feature on Disney International HD</li>
                <li>Feature in Crossword Book Store</li>
                <li>Digital National Media Coverage</li>
                <li>Physical Certificate & Trophy</li>
                <li>Interview on BB TV YouTube Channel</li>
            </ol>
            <p><strong>IMPORTANT NOTE:</strong> Awards #1-4 will be delivered within 30 days.</p>
            <p>Awards #5 & 6 will be presented to you at the <strong>National Awards & Exhibition Ceremony</strong>, tentatively scheduled for 30th March 2025. The final date, schedule, and venue will be confirmed in the third week of January 2025.</p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a> or call us at 1800 309 9917.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            SBWF 2024<br>
            India</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }
    private function sendAlertRank_21_50 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 14,
            'start'     => 20,
            'limit'     => 30,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $subject  = sprintf("Congratulations %s - You Are Now India's 50 Best-Selling Author at SBWF 2024!", $rank['author_name']);

            $content = "<p>Dear " . ucwords($rank['author_name'] ?? 'Author') . ",</p>
            <p>We are thrilled to announce that you have become India's Top 50 Best-Selling Young Author at the Summer Book Writing Festival 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p>As a winner, you will receive the following awards:</p>
            <ol>
                <li>Digital Certificate</li>
                <li>Feature on Disney International HD</li>
                <li>Feature in Crossword Book Store</li>
                <li>Physical Certificate & Trophy</li>
                <li>Interview on BB TV YouTube Channel</li>
            </ol>
            <p><strong>IMPORTANT NOTE:</strong> Awards #1-3 will be delivered within 30 working days.</p>
            <p>Awards #4 & 5 will be presented to you at the <strong>National Awards & Exhibition Ceremony</strong>, tentatively scheduled for 30th March 2025. The final date, schedule, and venue will be confirmed in the third week of January 2025.</p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a> or call us at 1800 309 9917.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            SBWF 2024<br>
            India</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }
    private function sendAlertRank_51_100 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 14,
            'start'     => 50,
            'limit'     => 50,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $subject  = sprintf("Congratulations %s - You Are Now India's Top 100 Best-Selling Author at SBWF 2024!", $rank['author_name']);

            $content = "<p>Dear " . ucwords($rank['author_name'] ?? 'Author') . ",</p>
            <p>We are thrilled to announce that you have become India's Top 100 Best-Selling Young Author at the Summer Book Writing Festival 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p>As a winner, you will receive the following awards:</p>
            <ol>
                <li>Digital Certificate</li>
                <li>Feature on Disney International HD</li>
                <li>Feature in Crossword Book Store</li>
                <li>Physical Certificate & Trophy</li>
            </ol>
            <p><strong>IMPORTANT NOTE:</strong> Awards #1-3 will be delivered within 30 working days.</p>
            <p>Awards #4 will be presented to you at the <strong>National Awards & Exhibition Ceremony</strong> tentatively scheduled for 30th March 2025. The final date, schedule, and venue will be confirmed in the third week of January 2025.</p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a> or call us at 1800 309 9917.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            SBWF 2024<br>
            India</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }

    private function sendUKAlertRank_1 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 15,
            'start'     => 0,
            'limit'     => 1,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $address_url = sprintf("https://www.yaf.bribooks.com/addressrequest?uid=%s&code=%s&eid=15",
             $user_info['id'],
             $user_info['verification_code']
            );

            $subject  = sprintf("Congratulations %s - You Are Now the UK's #1 Best-Selling Young Author at NYAF 2024!", $rank['author_name']);

            $content = "<p>Dear " .$rank['author_name'] . ",</p>
            <p>We are thrilled to announce that you have become the UK’s #1 Best-Selling Young Author at the National Young Authors’ Fair 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p><strong>IMPORTANT NOTE:</strong> Please provide your complete address for the delivery of the Kindle Paperwhite Kids by clicking the link below:</p>

            <p><a href=" . $address_url ." target='_blank'>Provide Address</a></p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a>.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            NYAF 2024<br>
            UK</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }

    private function sendUKAlertRank_2_3 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 15,
            'start'     => 1,
            'limit'     => 2,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $address_url = sprintf("https://www.yaf.bribooks.com/addressrequest?uid=%s&code=%s&eid=15",
             $user_info['id'],
             $user_info['verification_code']
            );

            $subject  = sprintf("Congratulations %s - You Are Now the UK's Top 3 Best-Selling Young Authors at NYAF 2024!", $rank['author_name']);

            $content = "<p>Dear " .$rank['author_name'] . ",</p>
            <p>We are thrilled to announce that you have become the UK’s Top 3 Best-Selling Young Authors at the National Young Authors’ Fair 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p><strong>IMPORTANT NOTE:</strong> Please provide your complete address for the delivery of the Echo Dot Kids (5th generation) by clicking the link below:</p>
            <p><a href=" . $address_url ." target='_blank'>Provide Address</a></p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a>.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            NYAF 2024<br>
            UK</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }

    private function sendUKAlertRank_4_5 () {
        $ranks = $this->ranking_country_model->get_all([
            'event_id'  => 15,
            'start'     => 3,
            'limit'     => 2,
            'order'     => 'DESC'
        ])['rows'] ?? [];

        foreach ($ranks as $rank) {
            if (empty($user_info = $this->user_model->get($rank['user_id'] ?? 0))) continue;

            $address_url = sprintf("https://www.yaf.bribooks.com/addressrequest?uid=%s&code=%s&eid=15",
             $user_info['id'],
             $user_info['verification_code']
            );

            $subject  = sprintf("Congratulations %s - You Are Now the UK's Top 5 Best-Selling Young Authors at NYAF 2024!", $rank['author_name']);

            $content = "<p>Dear " .$rank['author_name'] . ",</p>
            <p>We are thrilled to announce that you have become the UK’s Top 5 Best-Selling Young Authors at the National Young Authors’ Fair 2024! This incredible achievement is a testament to your excellence as an entrepreneurial author. Congratulations!</p>
            <p><strong>IMPORTANT NOTE:</strong> Please provide your complete details for the delivery of the £50 Amazon Pay eGift Card by clicking the link below:</p>
            <p><a href=" . $address_url ." target='_blank'>Provide Address</a></p>
            <p>For any queries, please contact us at <a href='mailto:support@bribooks.com'>support@bribooks.com</a>.</p>
            <p>Warm regards,</p>
            <p>Team BriBooks<br>
            NYAF 2024<br>
            UK</p>";

            self::email(
                $user_info['email'],
                $subject,
                $content,
                [],
                [],
                []
            );
        }
    }
}
