package excel

import (
	"fmt"

	"excel-worker/internal/symfony"
)

type Generator struct {
	outputDir string
}

func NewGenerator(outputDir string) *Generator {
	return &Generator{
		outputDir: outputDir,
	}
}

func (g *Generator) Generate(export symfony.ProcessingExport) (string, error) {
	filePath := fmt.Sprintf("%s/excel-export-%d.xlsx", g.outputDir, export.ID)

	return filePath, nil
}
