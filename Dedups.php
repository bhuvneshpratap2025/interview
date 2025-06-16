<?php

class Lead {
    public $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getId() {
        return $this->data['_id'];
    }

    public function getEmail() {
        return $this->data['email'];
    }

    public function getEntryDate() {
        return new DateTime($this->data['entryDate']);
    }

    public function compareTo(Lead $other): bool {
        $date1 = $this->getEntryDate();
        $date2 = $other->getEntryDate();

        return $date1 > $date2;
    }

    public function toArray(): array {
        return $this->data;
    }

    public function diff(Lead $other): array {
        $changes = [];
        foreach ($this->data as $key => $value) {
            if (isset($other->data[$key]) && $other->data[$key] !== $value) {
                $changes[] = [
                    'field' => $key,
                    'from' => $value,
                    'to' => $other->data[$key]
                ];
            }
        }
        return $changes;
    }
}

class Dedups{
    private array $leads;
    private $logs = [];

    private $byId = [];
    private $byEmail = [];

    public function __construct(array $leadData) {
		$this->leads = array_map(fn($data) => $data instanceof Lead ? $data : new Lead($data), $leadData);
    }

    public function removeduplicate(): array {
        foreach ($this->leads as $lead) {
            $this->processLead($lead);
        }
        // Return leads that are consistent across both maps
        $finalLeads = [];
        foreach ($this->byId as $id => $lead) {
            if (isset($this->byEmail[$lead->getEmail()]) && $this->byEmail[$lead->getEmail()] === $lead) {
                $finalLeads[$id] = $lead;
            }
        }
        return array_values(array_map(fn($l) => $l->toArray(), $finalLeads));
    }

    private function shouldReplace(Lead $existing, Lead $new): bool {
        $existingDate = $existing->getEntryDate();
        $newDate = $new->getEntryDate();
        return $newDate > $existingDate;
    }

    private function processLead(Lead $lead) {
        $id = $lead->getId();
        $email = $lead->getEmail();
        // By ID
        if (isset($this->byId[$id])) {
            if ($this->shouldReplace($this->byId[$id], $lead)) {
                $this->logChange($this->byId[$id], $lead);
                $this->byId[$id] = $lead;
            }
        } else {
            $this->byId[$id] = $lead;
        }

        // By Email
        if (isset($this->byEmail[$email])) {
            if ($this->shouldReplace($this->byEmail[$email], $lead)) {
                $this->logChange($this->byEmail[$email], $lead);
                $this->byEmail[$email] = $lead;
            }
        } else {
            $this->byEmail[$email] = $lead;
        }
    }

    private function logChange(Lead $old, Lead $new) {
        $diff = $old->diff($new);
        if (!empty($diff)) {
            $this->logs[] = [
                'source' => $old->toArray(),
                'replacement' => $new->toArray(),
                'changes' => $diff
            ];
        }
    }

    public function getLogs(): array {
        return $this->logs;
    }
}
