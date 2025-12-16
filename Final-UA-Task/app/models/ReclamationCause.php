<?php

class ReclamationCause extends Model
{
    protected string $table = 'Reclamation_Cause';
    protected string $primaryKey = 'id_reclamation'; // composite handled manually

    public function syncCauses(int $reclamationId, array $causeIds): void
    {
        $stmt = $this->db->prepare("DELETE FROM Reclamation_Cause WHERE id_reclamation = :rec");
        $stmt->execute(['rec' => $reclamationId]);

        $stmtInsert = $this->db->prepare(
            "INSERT INTO Reclamation_Cause (id_reclamation, id_cause) VALUES (:rec, :cause)"
        );
        foreach ($causeIds as $causeId) {
            $stmtInsert->execute(['rec' => $reclamationId, 'cause' => $causeId]);
        }
    }
}

