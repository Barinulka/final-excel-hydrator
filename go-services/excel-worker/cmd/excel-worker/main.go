package main

import (
	"fmt"
	"os"

	"excel-worker/internal/config"
	"excel-worker/internal/excel"
	"excel-worker/internal/symfony"
)

func main() {
	cfg, err := config.Load()
	if err != nil {
		fmt.Fprintln(os.Stderr, "config error:", err)
		os.Exit(1)
	}

	client := symfony.NewClient(cfg.SymfonyInternalBaseURL)
	generator := excel.NewGenerator(cfg.ExcelOutputDir)

	exports, err := client.GetPendingExports(10)
	if err != nil {
		fmt.Fprintln(os.Stderr, "worker error:", err)
		os.Exit(1)
	}

	fmt.Println("Excel worker started")
	fmt.Println("Pending exports:", len(exports))

	for _, export := range exports {
		fmt.Printf("Export #%d status=%s createdAt=%s\n", export.ID, export.Status, export.CreatedAt)
	}

	if len(exports) == 0 {
		fmt.Println("No pending exports")
		return
	}

	nextExport := exports[0]

	fmt.Printf("Marking export #%d as processing\n", nextExport.ID)

	processingExport, err := client.MarkExportProcessing(nextExport.ID)
	if err != nil {
		fmt.Fprintln(os.Stderr, "worker error:", err)
		os.Exit(1)
	}

	fmt.Printf(
		"Export #%d marked as %s startedAt=%s\n",
		processingExport.ID,
		processingExport.Status,
		processingExport.StartedAt,
	)

	filePath, err := generator.Generate(processingExport)
	if err != nil {
		failedExport, failErr := client.MarkExportFailed(processingExport.ID, err.Error())
		if failErr != nil {
			fmt.Fprintln(os.Stderr, "worker error:", failErr)
			os.Exit(1)
		}

		fmt.Printf(
			"Export #%d marked as %s error=%s failedAt=%s\n",
			failedExport.ID,
			failedExport.Status,
			failedExport.ErrorMessage,
			failedExport.FailedAt,
		)

		return
	}

	completedExport, err := client.MarkExportCompleted(processingExport.ID, filePath)
	if err != nil {
		fmt.Fprintln(os.Stderr, "worker error:", err)
		os.Exit(1)
	}

	fmt.Printf(
		"Export #%d marked as %s filePath=%s completedAt=%s\n",
		completedExport.ID,
		completedExport.Status,
		completedExport.FilePath,
		completedExport.CompletedAt,
	)
}
