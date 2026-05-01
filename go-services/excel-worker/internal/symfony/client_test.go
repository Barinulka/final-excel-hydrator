package symfony

import (
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"
)

func TestGetPendingExportsReturnsExports(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		if request.Method != http.MethodGet {
			t.Errorf("expected method %s, got %s", http.MethodGet, request.Method)
			return
		}

		if request.URL.Path != "/internal/excel-exports/pending" {
			t.Errorf("expected path %q, got %q", "/internal/excel-exports/pending", request.URL.Path)
			return
		}

		if request.URL.Query().Get("limit") != "10" {
			t.Errorf("expected limit query to be %q, got %q", "10", request.URL.Query().Get("limit"))
			return
		}

		response.Header().Set("Content-Type", "application/json")
		response.WriteHeader(http.StatusOK)

		_, _ = response.Write([]byte(`{
			"data": {
				"exports": [
					{
						"id": 15,
						"status": "pending",
						"calculationResultPayload": {
							"tables": [],
							"metrics": {},
							"warnings": []
						},
						"createdAt": "2026-05-01T10:00:00+00:00"
					}
				]
			}
		}`))
	}))
	defer server.Close()

	client := NewClient(server.URL)

	exports, err := client.GetPendingExports(10)

	if err != nil {
		t.Fatalf("expected pending exports, got error: %v", err)
	}

	if len(exports) != 1 {
		t.Fatalf("expected 1 export, got %d", len(exports))
	}

	if exports[0].ID != 15 {
		t.Fatalf("expected export ID %d, got %d", 15, exports[0].ID)
	}

	if exports[0].Status != "pending" {
		t.Fatalf("expected status %q, got %q", "pending", exports[0].Status)
	}
}

func TestGetPendingExportsReturnsRedirectError(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		http.Redirect(response, request, "/login", http.StatusFound)
	}))
	defer server.Close()

	client := NewClient(server.URL)

	_, err := client.GetPendingExports(10)

	if err == nil {
		t.Fatal("expected redirect error, got nil")
	}

	expectedError := "get pending exports: unexpected redirect to /login"
	if err.Error() != expectedError {
		t.Fatalf("expected error %q, got %q", expectedError, err.Error())
	}
}

func TestGetPendingExportsReturnsContentTypeError(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("Content-Type", "text/html; charset=utf-8")
		response.WriteHeader(http.StatusOK)

		_, _ = response.Write([]byte("<html></html>"))
	}))
	defer server.Close()

	client := NewClient(server.URL)

	_, err := client.GetPendingExports(10)

	if err == nil {
		t.Fatal("expected content type error, got nil")
	}

	expectedPart := "get pending exports: unexpected content type text/html"
	if !strings.Contains(err.Error(), expectedPart) {
		t.Fatalf("expected error to contain %q, got %q", expectedPart, err.Error())
	}
}

func TestMarkExportProcessingPostsToExpectedEndpoint(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		if request.Method != http.MethodPost {
			t.Errorf("expected method %s, got %s", http.MethodPost, request.Method)
			return
		}

		if request.URL.Path != "/internal/excel-exports/15/processing" {
			t.Errorf("expected path %q, got %q", "/internal/excel-exports/15/processing", request.URL.Path)
			return
		}

		response.Header().Set("Content-Type", "application/json")
		response.WriteHeader(http.StatusOK)

		_, _ = response.Write([]byte(`{
			"data": {
				"export": {
					"id": 15,
					"status": "processing",
					"startedAt": "2026-05-01T10:01:00+00:00"
				}
			}
		}`))
	}))
	defer server.Close()

	client := NewClient(server.URL)

	export, err := client.MarkExportProcessing(15)

	if err != nil {
		t.Fatalf("expected processing export, got error: %v", err)
	}

	if export.ID != 15 {
		t.Fatalf("expected export ID %d, got %d", 15, export.ID)
	}

	if export.Status != "processing" {
		t.Fatalf("expected status %q, got %q", "processing", export.Status)
	}
}

func TestMarkExportCompletedSendsFilePath(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		if request.Method != http.MethodPost {
			t.Errorf("expected method %s, got %s", http.MethodPost, request.Method)
			return
		}

		if request.URL.Path != "/internal/excel-exports/15/completed" {
			t.Errorf("expected path %q, got %q", "/internal/excel-exports/15/completed", request.URL.Path)
			return
		}

		var requestBody map[string]string
		if err := json.NewDecoder(request.Body).Decode(&requestBody); err != nil {
			t.Errorf("expected JSON request body, got error: %v", err)
			return
		}

		if requestBody["filePath"] != "exports/excel-export-15.xlsx" {
			t.Errorf("expected filePath %q, got %q", "exports/excel-export-15.xlsx", requestBody["filePath"])
			return
		}

		response.Header().Set("Content-Type", "application/json")
		response.WriteHeader(http.StatusOK)

		_, _ = response.Write([]byte(`{
			"data": {
				"export": {
					"id": 15,
					"status": "completed",
					"filePath": "exports/excel-export-15.xlsx",
					"completedAt": "2026-05-01T10:02:00+00:00"
				}
			}
		}`))
	}))
	defer server.Close()

	client := NewClient(server.URL)

	export, err := client.MarkExportCompleted(15, "exports/excel-export-15.xlsx")

	if err != nil {
		t.Fatalf("expected completed export, got error: %v", err)
	}

	if export.Status != "completed" {
		t.Fatalf("expected status %q, got %q", "completed", export.Status)
	}

	if export.FilePath != "exports/excel-export-15.xlsx" {
		t.Fatalf("expected filePath %q, got %q", "exports/excel-export-15.xlsx", export.FilePath)
	}
}

func TestMarkExportFailedSendsErrorMessage(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		if request.Method != http.MethodPost {
			t.Errorf("expected method %s, got %s", http.MethodPost, request.Method)
			return
		}

		if request.URL.Path != "/internal/excel-exports/15/failed" {
			t.Errorf("expected path %q, got %q", "/internal/excel-exports/15/failed", request.URL.Path)
			return
		}

		var requestBody map[string]string
		if err := json.NewDecoder(request.Body).Decode(&requestBody); err != nil {
			t.Errorf("expected JSON request body, got error: %v", err)
			return
		}

		if requestBody["errorMessage"] != "mock xlsx generation failed" {
			t.Errorf("expected errorMessage %q, got %q", "mock xlsx generation failed", requestBody["errorMessage"])
			return
		}

		response.Header().Set("Content-Type", "application/json")
		response.WriteHeader(http.StatusOK)

		_, _ = response.Write([]byte(`{
			"data": {
				"export": {
					"id": 15,
					"status": "failed",
					"errorMessage": "mock xlsx generation failed",
					"failedAt": "2026-05-01T10:02:00+00:00"
				}
			}
		}`))
	}))
	defer server.Close()

	client := NewClient(server.URL)

	export, err := client.MarkExportFailed(15, "mock xlsx generation failed")

	if err != nil {
		t.Fatalf("expected failed export, got error: %v", err)
	}

	if export.Status != "failed" {
		t.Fatalf("expected status %q, got %q", "failed", export.Status)
	}

	if export.ErrorMessage != "mock xlsx generation failed" {
		t.Fatalf("expected errorMessage %q, got %q", "mock xlsx generation failed", export.ErrorMessage)
	}
}
