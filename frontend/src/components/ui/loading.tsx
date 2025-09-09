import * as React from "react";
import { Loader2 } from "lucide-react";

import { cn } from "@/lib/utils";

interface LoadingProps extends React.HTMLAttributes<HTMLDivElement> {
  size?: "sm" | "md" | "lg";
  text?: string;
}

const Loading = React.forwardRef<HTMLDivElement, LoadingProps>(
  ({ className, size = "md", text, ...props }, ref) => {
    const sizeClasses = {
      sm: "h-4 w-4",
      md: "h-6 w-6",
      lg: "h-8 w-8",
    };

    return (
      <div
        ref={ref}
        className={cn("flex items-center justify-center gap-2", className)}
        {...props}
      >
        <Loader2 className={cn("animate-spin", sizeClasses[size])} />
        {text && <span className="text-sm text-muted-foreground">{text}</span>}
      </div>
    );
  }
);
Loading.displayName = "Loading";

interface LoadingSpinnerProps {
  size?: "sm" | "md" | "lg";
  className?: string;
}

const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({ 
  size = "md", 
  className 
}) => {
  const sizeClasses = {
    sm: "h-4 w-4",
    md: "h-6 w-6", 
    lg: "h-8 w-8",
  };

  return (
    <Loader2 className={cn("animate-spin", sizeClasses[size], className)} />
  );
};

interface LoadingSkeletonProps extends React.HTMLAttributes<HTMLDivElement> {
  lines?: number;
}

const LoadingSkeleton: React.FC<LoadingSkeletonProps> = ({ 
  lines = 3, 
  className,
  ...props 
}) => {
  return (
    <div className={cn("space-y-2", className)} {...props}>
      {Array.from({ length: lines }, (_, i) => (
        <div
          key={i}
          className="h-4 bg-muted animate-pulse rounded"
          style={{
            width: `${Math.random() * 40 + 60}%`,
          }}
        />
      ))}
    </div>
  );
};

interface LoadingCardProps {
  className?: string;
}

const LoadingCard: React.FC<LoadingCardProps> = ({ className }) => {
  return (
    <div className={cn("p-6 border rounded-lg bg-card", className)}>
      <div className="space-y-4">
        <div className="h-6 bg-muted animate-pulse rounded w-3/4" />
        <div className="h-4 bg-muted animate-pulse rounded w-1/2" />
        <div className="space-y-2">
          <div className="h-3 bg-muted animate-pulse rounded" />
          <div className="h-3 bg-muted animate-pulse rounded w-5/6" />
        </div>
      </div>
    </div>
  );
};

interface LoadingTableProps {
  rows?: number;
  columns?: number;
}

const LoadingTable: React.FC<LoadingTableProps> = ({ 
  rows = 5, 
  columns = 4 
}) => {
  return (
    <div className="space-y-3">
      {/* Header */}
      <div className="grid gap-4" style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}>
        {Array.from({ length: columns }, (_, i) => (
          <div key={i} className="h-4 bg-muted animate-pulse rounded" />
        ))}
      </div>
      
      {/* Rows */}
      {Array.from({ length: rows }, (_, rowIndex) => (
        <div 
          key={rowIndex} 
          className="grid gap-4" 
          style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}
        >
          {Array.from({ length: columns }, (_, colIndex) => (
            <div 
              key={colIndex} 
              className="h-4 bg-muted animate-pulse rounded"
              style={{
                width: `${Math.random() * 40 + 60}%`,
              }}
            />
          ))}
        </div>
      ))}
    </div>
  );
};

export { 
  Loading, 
  LoadingSpinner, 
  LoadingSkeleton, 
  LoadingCard, 
  LoadingTable 
};