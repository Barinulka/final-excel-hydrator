package symfony

type PendingExportsResponse struct {
	Data PendingExportsData `json:"data"`
}

type PendingExportsData struct {
	Exports []PendingExport `json:"exports"`
}

type PendingExport struct {
	ID                       int            `json:"id"`
	Status                   string         `json:"status"`
	CalculationResultPayload map[string]any `json:"calculationResultPayload"`
	CreatedAt                string         `json:"createdAt"`
}

type MarkProcessingResponse struct {
	Data MarkProcessingData `json:"data"`
}

type MarkProcessingData struct {
	Export ProcessingExport `json:"export"`
}

type ProcessingExport struct {
	ID        int    `json:"id"`
	Status    string `json:"status"`
	StartedAt string `json:"startedAt"`
}

type MarkCompletedResponse struct {
	Data MarkCompletedData `json:"data"`
}

type MarkCompletedData struct {
	Export CompletedExport `json:"export"`
}

type CompletedExport struct {
	ID          int    `json:"id"`
	Status      string `json:"status"`
	FilePath    string `json:"filePath"`
	CompletedAt string `json:"completedAt"`
}

type MarkFailedResponse struct {
	Data MarkFailedData `json:"data"`
}

type MarkFailedData struct {
	Export FailedExport `json:"export"`
}

type FailedExport struct {
	ID           int    `json:"id"`
	Status       string `json:"status"`
	ErrorMessage string `json:"errorMessage"`
	FailedAt     string `json:"failedAt"`
}
