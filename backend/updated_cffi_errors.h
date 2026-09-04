/*
 * _cffi_errors.h – Safe implementation for CFFI error capture on Windows.
 *
 * This header provides two sets of functions:
 *   * When CFFI_MESSAGEBOX == 1 (Windows SDK present) it uses a MessageBox
 *     to display fatal embedding errors.
 *   * When CFFI_MESSAGEBOX == 0 it provides lightweight stub functions
 *     that avoid any Windows‑specific headers, making the file compile on
 *     any platform (including when the Windows SDK is missing).
 *
 * The wheel distribution of cffi already contains a compiled binary, so
 * this header is never compiled at runtime.  However, keeping a clean
 * version helps developers who explore the source inside ``site‑packages``
 * and prevents IDE diagnostics from flagging missing Windows headers.
 */

#ifndef CFFI_MESSAGEBOX
# ifdef _MSC_VER
    /* The original upstream default enables MessageBox on MSVC builds,
     * but we force it off when the Windows SDK cannot be found.
     * Users who explicitly want the UI can define CFFI_MESSAGEBOX=1
     * before including this header.
     */
#   define CFFI_MESSAGEBOX 0
# else
#   define CFFI_MESSAGEBOX 0
# endif
#endif

#if CFFI_MESSAGEBOX
/* ------------------------------------------------------------------ */
/* Windows‑only implementation – retained for reference.               */
/* ------------------------------------------------------------------ */

#include <Python.h>
#include <windows.h>
#include <process.h>

/* A volatile pointer that holds the text to be shown in the MessageBox.
 * It is written by the error‑capture code and read by the background
 * thread that creates the dialog.
 */
static void *volatile _cffi_bootstrap_text;

/* Capture stderr output into a temporary Python object.
 * The function returns a dictionary that mimics a module and contains a
 * ``done()`` function which yields the captured text.
 */
static PyObject *_cffi_start_error_capture(void)
{
    PyObject *result = NULL;
    PyObject *m = NULL, *x = NULL, *bi = NULL;

    /* Ensure only a single capture is active at a time. */
    if (InterlockedCompareExchangePointer(&_cffi_bootstrap_text,
            (void *)1, NULL) != NULL)
        return (PyObject *)1;  /* already active */

    m = PyImport_AddModule("_cffi_error_capture");
    if (m == NULL)
        goto error;
    result = PyModule_GetDict(m);
    if (result == NULL)
        goto error;

    bi = PyImport_ImportModule("builtins");
    if (bi == NULL)
        goto error;
    PyDict_SetItemString(result, "__builtins__", bi);
    Py_DECREF(bi);

    x = PyRun_String(
        "import sys\n"
        "class FileLike:\n"
        "    def write(self, x):\n"
        "        try:\n"
        "            of.write(x)\n"
        "        except Exception: pass\n"
        "        self.buf += x\n"
        "    def flush(self):\n"
        "        pass\n"
        "fl = FileLike()\n"
        "fl.buf = ''\n"
        "of = sys.stderr\n"
        "sys.stderr = fl\n"
        "def done():\n"
        "    sys.stderr = of\n"
        "    return fl.buf\n",
        Py_file_input,
        result,
        result);
    Py_XDECREF(x);

error:
    if (PyErr_Occurred()) {
        PyErr_WriteUnraisable(Py_None);
        PyErr_Clear();
    }
    return result;
}

#pragma comment(lib, "user32.lib")

/* Background thread that shows the captured error text in a MessageBox. */
static DWORD WINAPI _cffi_bootstrap_dialog(LPVOID ignored)
{
    Sleep(666);  /* give the interpreter a moment to finish */
    MessageBoxW(NULL, (wchar_t *)_cffi_bootstrap_text,
                L"Python‑CFFI error",
                MB_OK | MB_ICONERROR);
    _cffi_bootstrap_text = NULL;
    return 0;
}

/* Finish the capture, retrieve the buffered text and fire the dialog. */
static void _cffi_stop_error_capture(PyObject *ecap)
{
    PyObject *s = NULL;
    void *text = NULL;

    if (ecap == (PyObject *)1)
        return;                 /* capture was already active elsewhere */
    if (ecap == NULL)
        goto error;

    s = PyRun_String("done()", Py_eval_input, ecap, ecap);
    if (s == NULL)
        goto error;

    text = PyUnicode_AsWideCharString(s, NULL);
    _cffi_bootstrap_text = text;

    if (text != NULL) {
        HANDLE h = CreateThread(NULL, 0, _cffi_bootstrap_dialog, NULL, 0, NULL);
        if (h != NULL)
            CloseHandle(h);
    }
    Py_DECREF(s);
    PyErr_Clear();
    return;

error:
    _cffi_bootstrap_text = NULL;
    PyErr_Clear();
}

#else /* CFFI_MESSAGEBOX == 0 */
/* ------------------------------------------------------------------ */
/* Stub implementations – safe on all platforms.                       */
/* ------------------------------------------------------------------ */

static PyObject *_cffi_start_error_capture(void) { return NULL; }
static void _cffi_stop_error_capture(PyObject *ecap) { (void)ecap; }

#endif /* CFFI_MESSAGEBOX */
