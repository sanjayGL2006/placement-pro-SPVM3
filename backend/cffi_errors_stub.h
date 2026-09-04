/*
 * cffi_errors_stub.h – Pure stub implementation for CFFI error capture.
 *
 * This header provides minimal stub functions that compile on any platform
 * without requiring the Windows SDK or Python development headers. It is
 * intended to replace the original `cffi/_cffi_errors.h` when the package
 * is installed via a binary wheel (the wheel already contains the compiled
 * C extension, so this header is never used at runtime).
 */

#ifndef CFFI_ERRORS_STUB_H
#define CFFI_ERRORS_STUB_H

/* Disable MessageBox UI – the stub never shows a dialog. */
#define CFFI_MESSAGEBOX 0

/* Forward‑declare a dummy PyObject type so the signatures match.
 * The real `PyObject` type is defined in Python.h, which we do not
 * include here to avoid the missing‑header problem.
 */
typedef void PyObject;

/* Stub function – returns NULL because there is no error capture.
 * The real implementation would capture stderr and later display a
 * MessageBox on Windows. In the stub we simply do nothing.
 */
#ifndef NULL
#define NULL ((void*)0)
#endif

static inline PyObject *_cffi_start_error_capture(void) {
    return NULL;
}
}

/* Stub function – does nothing. The argument is unused.
 */
static inline void _cffi_stop_error_capture(PyObject *ecap) {
    (void)ecap; /* suppress unused‑parameter warning */
}

#endif /* CFFI_ERRORS_STUB_H */
