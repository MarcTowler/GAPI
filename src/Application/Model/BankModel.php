<?php
namespace API\Model;

use API\Library;

class BankModel extends Library\BaseModel
{

	public function __construct()
	{
		parent::__construct();
	}
	
	public function withdraw($uuid, $amount)
	{
		$existing = $this->_link($uuid);

		if($existing === false)
		{
			return ['success' => false, 'reason' => "no account"];
		} else {
			//check the user actually has enough coins to withdraw
			$bal = $this->_db->prepare("SELECT balance FROM bank WHERE uid = :uuid");
			$bal->execute([':uuid' => $existing['uid']]);
			$balance = $bal->fetch(\PDO::FETCH_ASSOC);

			$balance = ($balance == false) ? 0 : $balance['balance'];

			if(($balance - $amount) < 0)
			{
				$this->_output = ['success' => false, 'reason' => "lack of funds", 'balance' => $total];
			} else {
				$stmt = $this->_db->prepare("UPDATE character SET pouch = pouch + :amount WHERE uid = :uuid");
				$stmt->execute(
					[
						':uuid'   => $existing['uid'],
						':amount' => $amount
					]
				);
				
				$bank = $this->_db->prepare("UPDATE bank SET balance = :newbal WHERE uid = :uuid");
				$bank->execute(
					[
						':uuid'   => $existing['uid'],
						':newbal' => ($balance - $amount)
					]
				);

				$this->_output = ['success' => true, 'amount' => $amount];
			}

			return $this->_output;
		}
	}

	public function deposit($uuid, $amount)
	{
		$uid      = $this->_getUID($uuid)['uid'];
		$existing = $this->_link($uid);

		if($existing == false)
		{
			return ['success' => false, 'reason' => "no account"];
		} else {
			$pouch = $this->_db->prepare("SELECT pouch FROM `character` WHERE uid = :uuid");
			$pouch->execute([':uuid' => $existing['uid']]);

			$total = $pouch->fetch(\PDO::FETCH_ASSOC);

			$total = ($total == false) ? 0 : $total['pouch'];

			if(($total - $amount) < 0)
			{
				$this->_output = ['success' => false, 'reason' => "lack of funds", 'balance' => $total];
			} else {
				try {
					$bank = $this->_db->prepare("UPDATE bank SET balance = balance + :deposit WHERE uid = :uuid");
					$bank->execute(
						[
							':uuid'    => $existing['uid'],
							':deposit' => $amount
						]
					);

					$pouch = $this->_db->prepare("UPDATE `character` SET pouch = :newbal WHERE uid = :uuid");
					$pouch->execute(
						[
							':uuid'   => $existing['uid'],
							':newbal' => ($total - $amount)
						]
					);

					$this->_output = ['success' => true, 'amount' => $amount];
				} catch(\PDOException $e) {
					$this->_output = ['success' => false, 'reason' => 'no bank account'];
				}
			}

			return $this->_output;
		}
	}

	public function open_account($uuid)
	{
		$uid      = $this->_getUID($uuid);
		$existing = $this->_link($uid['uid']);

		if($existing == false)
		{
			try {
				$stmt = $this->_db->prepare("INSERT INTO bank (uid, balance, protected) VALUES (:uuid, 0, 0)");
				$stmt->execute([':uuid' => $uid['uid']]);
			} catch(\PDOException $e) {
				var_dump($e->getMessage());
			}
			return ['success' => true];
		} else {
			return ['success' => false];
		}
	}

	public function check_balance($uuid)
	{
		$existing = $this->_link($this->_getUID($uuid)['uid']);

		if($existing === false)
		{
			return ['success' => false, 'reason' => "no account"];
		} else {
			$stmt = $this->_db->prepare("SELECT balance FROM bank WHERE uid = :uuid");
			$stmt->execute([':uuid' => $existing['uid']]);

			$this->_output = $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		return $this->_output;
	}

	private function _link($uuid)
	{
		$check = $this->_db->prepare("SELECT b.uid FROM bank b INNER JOIN `character` c ON b.uid = c.cid WHERE c.uid = :uuid");
		$check->execute([':uuid' => $uuid]);

		return $check->fetch(\PDO::FETCH_ASSOC);
	}

	private function _getUID($uuid)
	{
		$check = $this->_db->prepare("SELECT uid FROM users WHERE twitch_id = :uuid OR discord_id = :uuid");
		$check->execute([':uuid' => $uuid]);

		return $check->fetch(\PDO::FETCH_ASSOC);
	}
}