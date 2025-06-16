<?php
	class LeadRepository {
		private string $filePath;
		private string $outputPath;
		private array $leadsData = [];

		public function __construct(string $filePath,string $outputPath) {
			$this->filePath = $filePath;
			$this->outputPath = $outputPath;
		}

		public function load(): array {
			if (!file_exists($this->filePath)) {
				throw new Exception("Leads file not found.");
			}
			$json = file_get_contents($this->filePath);
			$data = json_decode($json, true);
			if (!isset($data['leads']) || !is_array($data['leads'])) {
				throw new Exception("Invalid leads format");
			}
			$this->leadsData = $data['leads'];
			return $this->leadsData;
		}

		public function save(array $leads): void {
			$jsonData = json_encode(['leads' => $leads], JSON_PRETTY_PRINT);
			file_put_contents($this->outputPath, $jsonData);
		}
	}
?>