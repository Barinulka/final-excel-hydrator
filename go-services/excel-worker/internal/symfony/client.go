package symfony

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"strings"
	"time"
)

type Client struct {
	baseURL    string
	httpClient *http.Client
}

func NewClient(baseURL string) *Client {
	return &Client{
		baseURL: strings.TrimRight(baseURL, "/"),
		httpClient: &http.Client{
			Timeout: 10 * time.Second,
			CheckRedirect: func(request *http.Request, via []*http.Request) error {
				return http.ErrUseLastResponse
			},
		},
	}
}

func (c *Client) GetPendingExports(limit int) ([]PendingExport, error) {
	url := fmt.Sprintf("%s/internal/excel-exports/pending?limit=%d", c.baseURL, limit)

	var pendingExportsResponse PendingExportsResponse
	if err := c.getJSON(url, &pendingExportsResponse); err != nil {
		return nil, fmt.Errorf("get pending exports: %w", err)
	}

	return pendingExportsResponse.Data.Exports, nil
}

func (c *Client) MarkExportProcessing(exportID int) (ProcessingExport, error) {
	url := fmt.Sprintf("%s/internal/excel-exports/%d/processing", c.baseURL, exportID)

	var markProcessingResponse MarkProcessingResponse
	if err := c.postJSON(url, nil, &markProcessingResponse); err != nil {
		return ProcessingExport{}, fmt.Errorf("mark export processing: %w", err)
	}

	return markProcessingResponse.Data.Export, nil
}

func (c *Client) MarkExportCompleted(exportID int, filePath string) (CompletedExport, error) {
	url := fmt.Sprintf("%s/internal/excel-exports/%d/completed", c.baseURL, exportID)

	requestBody := map[string]string{
		"filePath": filePath,
	}

	var markCompletedResponse MarkCompletedResponse
	if err := c.postJSON(url, requestBody, &markCompletedResponse); err != nil {
		return CompletedExport{}, fmt.Errorf("mark export completed: %w", err)
	}

	return markCompletedResponse.Data.Export, nil
}

func (c *Client) MarkExportFailed(exportID int, errorMessage string) (FailedExport, error) {
	url := fmt.Sprintf("%s/internal/excel-exports/%d/failed", c.baseURL, exportID)

	requestBody := map[string]string{
		"errorMessage": errorMessage,
	}

	var markFailedResponse MarkFailedResponse
	if err := c.postJSON(url, requestBody, &markFailedResponse); err != nil {
		return FailedExport{}, fmt.Errorf("mark export failed: %w", err)
	}

	return markFailedResponse.Data.Export, nil
}

func (c *Client) getJSON(url string, responseBody any) error {
	response, err := c.httpClient.Get(url)
	if err != nil {
		return err
	}
	defer response.Body.Close()

	return decodeJSONResponse(response, http.StatusOK, responseBody)
}

func (c *Client) postJSON(url string, requestBody any, responseBody any) error {
	var reader io.Reader

	if requestBody != nil {
		encodedBody, err := json.Marshal(requestBody)
		if err != nil {
			return fmt.Errorf("encode request body: %w", err)
		}

		reader = bytes.NewReader(encodedBody)
	}

	response, err := c.httpClient.Post(url, "application/json", reader)
	if err != nil {
		return err
	}
	defer response.Body.Close()

	return decodeJSONResponse(response, http.StatusOK, responseBody)
}

func decodeJSONResponse(response *http.Response, expectedStatusCode int, responseBody any) error {
	if response.StatusCode >= http.StatusMultipleChoices && response.StatusCode < http.StatusBadRequest {
		return fmt.Errorf("unexpected redirect to %s", response.Header.Get("Location"))
	}

	if response.StatusCode != expectedStatusCode {
		return fmt.Errorf("unexpected status %d", response.StatusCode)
	}

	contentType := response.Header.Get("Content-Type")
	if !strings.Contains(contentType, "application/json") {
		return fmt.Errorf("unexpected content type %s", contentType)
	}

	if err := json.NewDecoder(response.Body).Decode(responseBody); err != nil {
		return fmt.Errorf("decode response: %w", err)
	}

	return nil
}
