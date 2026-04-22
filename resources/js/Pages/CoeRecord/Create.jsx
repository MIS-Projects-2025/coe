import React, { useState } from "react";
import { router } from "@inertiajs/react";

import {
    Card,
    CardContent,
    CardHeader,
    CardFooter,
    CardTitle,
    CardDescription,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Separator } from "@/components/ui/separator";
import { Progress } from "@/components/ui/progress";
import {
    FileText,
    Send,
    User,
    Upload,
    AlertCircle,
    Building,
    Calendar,
    Briefcase,
    Hash,
    X,
    ChevronDown,
    ChevronUp,
} from "lucide-react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Combobox } from "@/Components/ui/combobox";

export default function Create({ employee, purposes }) {
    const [loading, setLoading] = useState(false);
    const [attachment, setAttachment] = useState(null);
    const [attachmentName, setAttachmentName] = useState("");
    const [formData, setFormData] = useState({
        purpose: "",
        coe_type: "",
    });
    const [errors, setErrors] = useState({});
    const [uploadProgress, setUploadProgress] = useState(0);

    // ✅ NEW STATE (collapse)
    const [showEmployeeInfo, setShowEmployeeInfo] = useState(true);

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/jpg",
                "application/pdf",
            ];
            const maxSize = 5 * 1024 * 1024;

            if (!allowedTypes.includes(file.type)) {
                setErrors({
                    attachment: "Please upload JPEG, PNG, or PDF file only",
                });
                return;
            }

            if (file.size > maxSize) {
                setErrors({ attachment: "File size must be less than 5MB" });
                return;
            }

            setAttachment(file);
            setAttachmentName(file.name);
            setErrors({});

            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                setUploadProgress(progress);
                if (progress >= 100) clearInterval(interval);
            }, 100);
        }
    };

    const removeAttachment = () => {
        setAttachment(null);
        setAttachmentName("");
        setUploadProgress(0);
        const fileInput = document.getElementById("attachment-input");
        if (fileInput) fileInput.value = "";
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const newErrors = {};
        if (!formData.purpose) newErrors.purpose = "Purpose is required";
        if (!attachment) newErrors.attachment = "Proof of request is required";

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setLoading(true);

        const data = new FormData();
        data.append("purpose", formData.purpose);
        data.append("coe_type", formData.coe_type);
        data.append("attachment", attachment);

        router.post("/coe-record", data, {
            onSuccess: () => setLoading(false),
            onError: (err) => {
                setErrors(err);
                setLoading(false);
            },
        });
    };

    const formatDate = (date) => {
        if (!date) return "N/A";
        return new Date(date).toLocaleDateString("en-US", {
            year: "numeric",
            month: "long",
            day: "numeric",
        });
    };

    const coeTypeOptions = [
        {
            value: "with_compensation",
            label: "With Compensation",
            description: "Includes salary and compensation details",
        },
        {
            value: "without_compensation",
            label: "Without Compensation",
            description: "Basic employment details only",
        },
    ];

    const purposeOptions = purposes.map((p) => ({
        value: p.purpose,
        label: p.purpose,
        description: p.description || null,
    }));

    return (
        <AuthenticatedLayout title="Request Certificate of Employment">
            <div className="py-8 px-4">
                <div className="container mx-auto max-w-4xl">
                    <form onSubmit={handleSubmit}>
                        <Card>
                            <CardHeader>
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-primary/10 rounded-xl">
                                        <FileText className="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <CardTitle>Request COE</CardTitle>
                                        <CardDescription>
                                            Fill out the form below
                                        </CardDescription>
                                    </div>
                                </div>
                            </CardHeader>

                            <CardContent className="space-y-6 pt-6">
                                {Object.keys(errors).length > 0 && (
                                    <Alert variant="destructive">
                                        <AlertCircle className="h-4 w-4" />
                                        <AlertDescription>
                                            Please fix the errors below
                                        </AlertDescription>
                                    </Alert>
                                )}

                                {/* ✅ COLLAPSIBLE EMPLOYEE INFO */}
                                <Card>
                                    <CardHeader className="pb-3">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <User className="h-4 w-4 text-muted-foreground" />
                                                <CardTitle className="text-base">
                                                    Employee Information
                                                </CardTitle>
                                            </div>

                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                onClick={() =>
                                                    setShowEmployeeInfo(
                                                        !showEmployeeInfo,
                                                    )
                                                }
                                            >
                                                {showEmployeeInfo ? (
                                                    <ChevronUp className="h-4 w-4" />
                                                ) : (
                                                    <ChevronDown className="h-4 w-4" />
                                                )}
                                            </Button>
                                        </div>
                                    </CardHeader>

                                    {showEmployeeInfo && (
                                        <CardContent>
                                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div className="flex items-start gap-2">
                                                    <Hash className="h-4 w-4 text-muted-foreground mt-0.5" />
                                                    <div>
                                                        <Label className="text-xs text-muted-foreground">
                                                            Employee ID
                                                        </Label>
                                                        <p className="text-sm font-medium">
                                                            {employee?.employid ||
                                                                "N/A"}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-start gap-2">
                                                    <User className="h-4 w-4 text-muted-foreground mt-0.5" />
                                                    <div>
                                                        <Label className="text-xs text-muted-foreground">
                                                            Full Name
                                                        </Label>
                                                        <p className="text-sm font-medium">
                                                            {employee?.emp_name ||
                                                                "N/A"}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-start gap-2">
                                                    <Briefcase className="h-4 w-4 text-muted-foreground mt-0.5" />
                                                    <div>
                                                        <Label className="text-xs text-muted-foreground">
                                                            Position
                                                        </Label>
                                                        <p className="text-sm">
                                                            {employee?.position ||
                                                                "N/A"}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-start gap-2">
                                                    <Calendar className="h-4 w-4 text-muted-foreground mt-0.5" />
                                                    <div>
                                                        <Label className="text-xs text-muted-foreground">
                                                            Date Hired
                                                        </Label>
                                                        <p className="text-sm">
                                                            {formatDate(
                                                                employee?.date_hired,
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-start gap-2">
                                                    <Building className="h-4 w-4 text-muted-foreground mt-0.5" />
                                                    <div>
                                                        <Label className="text-xs text-muted-foreground">
                                                            Department
                                                        </Label>
                                                        <p className="text-sm">
                                                            {employee?.department ||
                                                                "N/A"}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div className="flex items-start gap-2">
                                                    <Building className="h-4 w-4 text-muted-foreground mt-0.5" />
                                                    <div>
                                                        <Label className="text-xs text-muted-foreground">
                                                            Production Line
                                                        </Label>
                                                        <p className="text-sm">
                                                            {employee?.prodline ||
                                                                "N/A"}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </CardContent>
                                    )}
                                </Card>

                                <Separator />
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Purpose Selection */}
                                    <div className="space-y-2">
                                        <Label>
                                            Purpose{" "}
                                            <span className="text-destructive">
                                                *
                                            </span>
                                        </Label>

                                        <Combobox
                                            options={purposeOptions}
                                            value={formData.purpose}
                                            onChange={(val) =>
                                                setFormData({
                                                    ...formData,
                                                    purpose: val,
                                                })
                                            }
                                            placeholder="Select Purpose"
                                        />

                                        {formData.purpose && (
                                            <p className="text-xs text-muted-foreground">
                                                {
                                                    purposeOptions.find(
                                                        (o) =>
                                                            o.value ===
                                                            formData.purpose,
                                                    )?.description
                                                }
                                            </p>
                                        )}
                                    </div>

                                    {/* COE Type */}
                                    <div className="space-y-2">
                                        <Label>COE Type</Label>

                                        <Combobox
                                            options={coeTypeOptions}
                                            value={formData.coe_type}
                                            onChange={(val) =>
                                                setFormData({
                                                    ...formData,
                                                    coe_type: val,
                                                })
                                            }
                                            placeholder="Select COE Type"
                                        />

                                        {/* Description */}
                                        {formData.coe_type && (
                                            <p className="text-xs text-muted-foreground">
                                                {
                                                    coeTypeOptions.find(
                                                        (o) =>
                                                            o.value ===
                                                            formData.coe_type,
                                                    )?.description
                                                }
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <Separator />

                                {/* File Attachment */}
                                <div className="space-y-3">
                                    <Label>
                                        Proof of Request{" "}
                                        <span className="text-destructive">
                                            *
                                        </span>
                                    </Label>

                                    {!attachmentName ? (
                                        <div className="border-2 border-dashed rounded-lg p-6 text-center transition-colors hover:border-primary">
                                            <Upload className="h-8 w-8 text-muted-foreground mx-auto mb-3" />
                                            <p className="text-sm text-muted-foreground mb-2">
                                                Drag and drop or click to upload
                                            </p>
                                            <Input
                                                id="attachment-input"
                                                type="file"
                                                onChange={handleFileChange}
                                                accept=".pdf,.jpg,.jpeg,.png"
                                                className="cursor-pointer w-full max-w-xs mx-auto"
                                            />
                                            <p className="text-xs text-muted-foreground mt-3">
                                                Accepted formats: JPEG, PNG, PDF
                                                (Max 5MB)
                                            </p>
                                        </div>
                                    ) : (
                                        <Card className="bg-success/10 border-success/20">
                                            <CardContent className="p-4">
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-3">
                                                        <FileText className="h-8 w-8 text-success" />
                                                        <div>
                                                            <p className="text-sm font-medium">
                                                                {attachmentName}
                                                            </p>
                                                            <p className="text-xs text-muted-foreground">
                                                                Ready to upload
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={
                                                            removeAttachment
                                                        }
                                                        className="text-destructive hover:text-destructive/80"
                                                    >
                                                        <X className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                                {uploadProgress > 0 &&
                                                    uploadProgress < 100 && (
                                                        <div className="mt-3">
                                                            <Progress
                                                                value={
                                                                    uploadProgress
                                                                }
                                                                className="h-1"
                                                            />
                                                            <p className="text-xs text-muted-foreground mt-1">
                                                                {uploadProgress}
                                                                % uploaded
                                                            </p>
                                                        </div>
                                                    )}
                                            </CardContent>
                                        </Card>
                                    )}
                                    {errors.attachment && (
                                        <p className="text-sm text-destructive">
                                            {errors.attachment}
                                        </p>
                                    )}
                                </div>
                            </CardContent>

                            <CardFooter>
                                <Button type="submit" className="w-full">
                                    {loading
                                        ? "Submitting..."
                                        : "Submit Request"}
                                </Button>
                            </CardFooter>
                        </Card>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
