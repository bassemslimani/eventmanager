/**
 * Professional Badge Generator
 * Generates print-ready PDF badges with front and back pages
 */

// @ts-ignore - qrcode types not available
import QRCode from 'qrcode';

export interface BadgeData {
    success: boolean;
    attendee: {
        name: string;
        name_ar: string | null;
        company: string | null;
        company_ar: string | null;
        type: string;
        email: string;
        qr_uuid: string;
        phone?: string;
    };
    event: {
        name: string;
        name_ar: string | null;
        logo_url: string | null;
        date: string;
        location: string;
    };
    template: {
        front_template_url: string | null;
        back_template_url: string | null;
        badge_width: number;  // legacy px
        badge_height: number;  // legacy px
        badge_width_cm: number;  // new cm measurement
        badge_height_cm: number;  // new cm measurement
        font_family: string;
        primary_color: string;
        secondary_color: string;
        terms_and_conditions: string | null;
        show_qr_code: boolean;
        show_logo: boolean;
        show_category_badge: boolean;
        elements?: any[];  // element-based layout
    };
    qr_code_svg: string;
}

/**
 * Convert SVG to Data URL
 */
function svgToDataURL(svgString: string): string {
    return 'data:image/svg+xml;base64,' + btoa(svgString);
}

/**
 * Generate print-ready PDF badge (single page only)
 */
export async function generatePDFBadge(data: BadgeData): Promise<void> {
    try {
        console.log('Importing jsPDF...');
        const { jsPDF } = await import('jspdf');

        console.log('Creating PDF document...');
        // Use cm as the unit for standard badge size (8.5cm x 12.5cm)
        const badgeWidthCm = data.template.badge_width_cm || 8.5;
        const badgeHeightCm = data.template.badge_height_cm || 12.5;

        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'cm',
            format: [badgeWidthCm, badgeHeightCm],
        });

        // Generate Badge Page with positioned elements
        console.log('Generating badge with designer elements...');
        await generateBadgeFront(pdf, data);
        console.log('Badge generated successfully');

        // Download the PDF
        const filename = `badge-${data.attendee.name.replace(/\s+/g, '-')}.pdf`;
        console.log('Saving PDF as:', filename);
        pdf.save(filename);
        console.log('PDF saved successfully');
    } catch (error) {
        console.error('Error in generatePDFBadge:', error);
        throw error;
    }
}

/**
 * Generate Badge using Designer Elements
 */
async function generateBadgeFront(pdf: any, data: BadgeData): Promise<void> {
    const { template, attendee, event } = data;
    const widthCm = template.badge_width_cm || 8.5;
    const heightCm = template.badge_height_cm || 12.5;

    console.log('Badge dimensions:', widthCm, 'cm x', heightCm, 'cm');

    // Draw background template if available
    if (template.front_template_url) {
        console.log('Loading template:', template.front_template_url);
        try {
            await addImageToPDF(pdf, template.front_template_url, 0, 0, widthCm, heightCm);
            console.log('Template loaded successfully');
        } catch (error) {
            console.error('Failed to load template:', error);
            // Draw white background as fallback
            pdf.setFillColor(255, 255, 255);
            pdf.rect(0, 0, widthCm, heightCm, 'F');
            console.log('Using white background as fallback');
        }
    } else {
        console.log('No template, using white background');
        // White background
        pdf.setFillColor(255, 255, 255);
        pdf.rect(0, 0, widthCm, heightCm, 'F');
    }

    // Use designer elements if available
    if (template.elements && template.elements.length > 0) {
        console.log('Using designer elements:', template.elements.length);
        await renderDesignerElements(pdf, template.elements, data);
    } else {
        console.log('No designer elements found, using legacy layout');
        // Fallback to legacy hardcoded layout
        await renderLegacyLayout(pdf, data);
    }
}

/**
 * Render elements from designer
 */
async function renderDesignerElements(pdf: any, elements: any[], data: BadgeData): Promise<void> {
    const { attendee, event, qr_code_svg, template } = data;

    // Helper to get field value
    const getFieldValue = (field: string): string => {
        if (field.startsWith('static:')) {
            return field.substring(7);
        }

        const fieldMap: Record<string, string> = {
            'event.name': event.name,
            'event.date': event.date,
            'event.location': event.location,
            'attendee.name': attendee.name,
            'attendee.company': attendee.company || 'Freelancer',
            'attendee.category': attendee.type.charAt(0).toUpperCase() + attendee.type.slice(1),
            'attendee.email': attendee.email,
            'attendee.phone': attendee.phone || '',
            'attendee.qr_uuid': attendee.qr_uuid,
        };

        return fieldMap[field] || field;
    };

    // Render each element
    for (const element of elements) {
        if (!element.visible) continue;

        console.log('Rendering element:', element.type, element.label);

        if (element.type === 'text') {
            // Render text element
            const text = getFieldValue(element.field);
            const fontFamily = template.font_family || 'helvetica';
            const fontWeight = element.fontWeight === 'bold' ? 'bold' : 'normal';

            pdf.setFont(fontFamily, fontWeight);
            pdf.setFontSize(element.fontSize || 16);
            pdf.setTextColor(...hexToRGB(element.color || '#000000'));

            // Calculate position based on alignment
            let xPos = element.x;
            const align = element.align || 'left';

            pdf.text(text, xPos, element.y, {
                align: align,
                maxWidth: element.maxWidth || undefined
            });

        } else if (element.type === 'qrcode') {
            // Render QR code
            console.log('Adding QR code at', element.x, element.y);
            const qrWidth = element.width || 2.5;
            const qrHeight = element.height || 2.5;

            // Center the QR code on the specified position
            const qrX = element.x - qrWidth / 2;
            const qrY = element.y - qrHeight / 2;

            try {
                // Generate QR code as PNG (more reliable than SVG in PDF)
                const qrData = attendee.qr_uuid;
                const qrDataUrl = await QRCode.toDataURL(qrData, {
                    width: 800,  // High resolution for print quality
                    margin: 1,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    }
                });

                console.log('Generated QR code PNG for PDF');
                pdf.addImage(qrDataUrl, 'PNG', qrX, qrY, qrWidth, qrHeight);
                console.log('QR code added successfully to PDF');
            } catch (error) {
                console.error('Failed to add QR code:', error);
            }

        } else if (element.type === 'logo') {
            // Render logo
            if (event.logo_url) {
                console.log('Adding logo at', element.x, element.y);
                const logoWidth = element.width || 3;
                const logoHeight = element.height || 1.5;

                // Center the logo on the specified position
                const logoX = element.x - logoWidth / 2;
                const logoY = element.y - logoHeight / 2;

                try {
                    await addImageToPDFWithAspectRatio(pdf, event.logo_url, logoX, logoY, logoWidth, logoHeight);
                    console.log('Logo added successfully');
                } catch (error) {
                    console.error('Failed to add logo:', error);
                }
            }
        }
    }
}

/**
 * Legacy hardcoded layout (fallback)
 */
async function renderLegacyLayout(pdf: any, data: BadgeData): Promise<void> {
    const { template, attendee, event } = data;
    const widthCm = template.badge_width_cm || 8.5;

    // Add Event Logo if available
    if (template.show_logo && event.logo_url) {
        try {
            await addImageToPDFWithAspectRatio(pdf, event.logo_url, widthCm / 2 - 2, 0.5, 4, 2);
        } catch (error) {
            console.error('Failed to load event logo:', error);
        }
    }

    // Event Title
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(16);
    pdf.setTextColor(...hexToRGB(template.secondary_color));
    pdf.text(event.name, widthCm / 2, 3, { align: 'center' });

    // Attendee Name
    pdf.setFont(template.font_family || 'helvetica', 'bold');
    pdf.setFontSize(24);
    pdf.setTextColor(...hexToRGB(template.primary_color));
    pdf.text(attendee.name, widthCm / 2, 5.5, { align: 'center' });

    // Company
    if (attendee.company) {
        pdf.setFont(template.font_family || 'helvetica', 'normal');
        pdf.setFontSize(16);
        pdf.setTextColor(...hexToRGB(template.secondary_color));
        pdf.text(attendee.company, widthCm / 2, 6.5, { align: 'center' });
    }

    // QR Code
    if (template.show_qr_code) {
        const qrSizeCm = 2.5;
        const qrX = widthCm / 2 - qrSizeCm / 2;
        const qrY = 8.5;

        try {
            // Generate QR code as PNG
            const qrDataUrl = await QRCode.toDataURL(attendee.qr_uuid, {
                width: 800,
                margin: 1,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            });
            pdf.addImage(qrDataUrl, 'PNG', qrX, qrY, qrSizeCm, qrSizeCm);
        } catch (error) {
            console.error('Failed to add QR code:', error);
        }
    }
}

/**
 * Helper function to add image to PDF
 */
async function addImageToPDF(pdf: any, imageUrl: string, x: number, y: number, width: number, height: number): Promise<void> {
    return new Promise((resolve, reject) => {
        const img = new Image();

        // Set timeout for image loading
        const timeout = setTimeout(() => {
            reject(new Error('Image load timeout'));
        }, 10000); // 10 second timeout

        img.crossOrigin = 'anonymous';

        img.onload = () => {
            clearTimeout(timeout);
            try {
                // Detect image format from URL
                const format = imageUrl.toLowerCase().endsWith('.png') ? 'PNG' : 'JPEG';
                pdf.addImage(img, format, x, y, width, height);
                resolve();
            } catch (error) {
                reject(error);
            }
        };

        img.onerror = (error) => {
            clearTimeout(timeout);
            reject(error);
        };

        // Add timestamp to prevent caching issues
        const separator = imageUrl.includes('?') ? '&' : '?';
        img.src = imageUrl + separator + 't=' + Date.now();
    });
}

/**
 * Helper function to add image to PDF with aspect ratio preservation
 */
async function addImageToPDFWithAspectRatio(pdf: any, imageUrl: string, x: number, y: number, maxWidth: number, maxHeight: number): Promise<void> {
    return new Promise((resolve, reject) => {
        const img = new Image();

        // Set timeout for image loading
        const timeout = setTimeout(() => {
            reject(new Error('Image load timeout'));
        }, 10000); // 10 second timeout

        img.crossOrigin = 'anonymous';

        img.onload = () => {
            clearTimeout(timeout);
            try {
                // Calculate dimensions preserving aspect ratio
                const imgAspectRatio = img.width / img.height;
                const maxAspectRatio = maxWidth / maxHeight;

                let renderWidth = maxWidth;
                let renderHeight = maxHeight;

                if (imgAspectRatio > maxAspectRatio) {
                    // Image is wider than container
                    renderHeight = maxWidth / imgAspectRatio;
                } else {
                    // Image is taller than container
                    renderWidth = maxHeight * imgAspectRatio;
                }

                // Center the image in the allocated space
                const offsetX = x + (maxWidth - renderWidth) / 2;
                const offsetY = y + (maxHeight - renderHeight) / 2;

                // Detect image format from URL
                const format = imageUrl.toLowerCase().endsWith('.png') ? 'PNG' : 'JPEG';
                pdf.addImage(img, format, offsetX, offsetY, renderWidth, renderHeight);

                console.log(`Image loaded: ${img.width}x${img.height}, rendered as: ${renderWidth.toFixed(2)}x${renderHeight.toFixed(2)}`);
                resolve();
            } catch (error) {
                reject(error);
            }
        };

        img.onerror = (error) => {
            clearTimeout(timeout);
            reject(error);
        };

        // Add timestamp to prevent caching issues
        const separator = imageUrl.includes('?') ? '&' : '?';
        img.src = imageUrl + separator + 't=' + Date.now();
    });
}

/**
 * Convert hex color to RGB
 */
function hexToRGB(hex: string): [number, number, number] {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result
        ? [parseInt(result[1], 16), parseInt(result[2], 16), parseInt(result[3], 16)]
        : [0, 0, 0];
}
