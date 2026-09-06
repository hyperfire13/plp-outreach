function filenameFromDisposition(disposition, fallbackName) {
    if (!disposition) {
        return fallbackName;
    }

    const utf8Match = disposition.match(/filename\*=UTF-8''([^;]+)/i);

    if (utf8Match?.[1]) {
        return decodeURIComponent(utf8Match[1].replace(/["']/g, ""));
    }

    const filenameMatch = disposition.match(/filename="?([^";]+)"?/i);

    return filenameMatch?.[1] || fallbackName;
}

export function downloadResponse(response, fallbackName = "download") {
    const disposition = response.headers?.["content-disposition"] || "";
    const filename = filenameFromDisposition(disposition, fallbackName);
    const url = URL.createObjectURL(response.data);
    const link = document.createElement("a");

    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}
